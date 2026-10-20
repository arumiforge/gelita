/**
 * WebGL screen effects for the narrated scenes (owner, 19 October 2026):
 * drifting fog, lantern sparks, light rays, and a bloom glow, drawn with
 * three.js on one transparent canvas over the scene. game/narrator.js loads
 * this module only for a root with `data-fx3d` (the three.js URL, from
 * screen_fx_attr()) and only without prefers-reduced-motion.
 *
 * Pipeline per frame (all shaders below, three.js core only, no add-ons):
 *   scene  fog (premultiplied alpha) + rays + sparks (additive) → target
 *   bloom  bright pass → blur H/V twice at 1/4 size
 *   output scene + bloom → canvas, alpha = max(fog, light) so the light
 *          brightens the page underneath instead of covering it
 *
 * Options for pages outside the story scenes (game/cine.js): `light` puts the
 * rays' source and halo on a point of the background picture (the welcome
 * page's sun), and `quality` starts one step lower, so a first page does not
 * spend four slow seconds at full resolution before the watchdog steps down.
 *
 * Script effects (`dialogues.effect`, setEffect()):
 *   fog       the mist thickens, the sparks fade
 *   fog-lift  thick mist clears, sparks and a little light come through
 *   glow      light rays from above with a warm halo, brighter sparks
 *   flash     a short burst of sparks and light (the CSS flash stays)
 *   dim       the sparks dim (the CSS dim stays)
 *   sun       rays from the picture's light source ease in over 4 s and stay soft
 *   shake / none  back to the calm base: a thin mist and slow sparks
 * While the layer runs the root has `.has-fx3d`, and cinematic.css hides the
 * CSS fog, fog-lift, and glow and the CSS light motes it replaces.
 *
 * Safety: no WebGL 2, a failed three.js load, a lost WebGL context, or frames
 * that stay slow after two quality steps remove the canvas and the class, so
 * the CSS effects take over again. Parallax (game/parallax.js) shifts the
 * fog a little and the sparks more, as near layers.
 */
import { parallaxValue } from './parallax.js';

const BASE = { fog: 0.2, rays: 0, spark: 0.75 };
const SPARKS = 110;
const SLOW_MS = 45;          // a frame slower than this (about 22 fps) counts as slow
const SLOW_FOR_MS = 4000;    // slow this long → one quality step down
// Pixels per CSS pixel (capped by the screen's own) and bloom, per step down
const QUALITY = [
  { ratio: 2, bloom: true },
  { ratio: 0.75, bloom: true },
  { ratio: 0.5, bloom: false },
];

const QUAD_VERTEX = /* glsl */ `
varying vec2 vUv;
void main() {
  vUv = uv;
  gl_Position = vec4(position.xy, 0.0, 1.0);
}`;

const NOISE = /* glsl */ `
float hash(vec2 p) {
  p = fract(p * vec2(123.34, 456.21));
  p += dot(p, p + 45.32);
  return fract(p.x * p.y);
}
float noise(vec2 p) {
  vec2 i = floor(p);
  vec2 f = fract(p);
  vec2 u = f * f * (3.0 - 2.0 * f);
  return mix(mix(hash(i), hash(i + vec2(1.0, 0.0)), u.x), mix(hash(i + vec2(0.0, 1.0)), hash(i + vec2(1.0, 1.0)), u.x), u.y);
}
float fbm(vec2 p) {
  float v = 0.0;
  float a = 0.5;
  for (int k = 0; k < 5; k++) {
    v += a * noise(p);
    p = p * 2.03 + vec2(17.1, 9.2);
    a *= 0.5;
  }
  return v;
}`;

const FOG_FRAGMENT = /* glsl */ `
uniform float uTime;
uniform float uFog;
uniform float uAspect;
uniform vec2 uShift;
varying vec2 vUv;
${NOISE}
void main() {
  vec2 p = vec2(vUv.x * uAspect, vUv.y) * 2.2 + uShift;
  float t = uTime * 0.035;
  float n = fbm(p + vec2(t * 1.6, t * 0.4));
  float m = fbm(p * 1.7 - vec2(t * 1.1, -t * 0.3) + n);
  float body = smoothstep(0.35, 0.95, n * 0.6 + m * 0.6);
  // Thicker along the bottom and the sides, thin around the faces and the HUD
  float low = 0.55 + 0.45 * smoothstep(0.7, 0.0, vUv.y);
  float side = 0.6 + 0.4 * pow(abs(vUv.x - 0.5) * 2.0, 1.5);
  float a = clamp(body * low * side * uFog * 1.6, 0.0, 0.9);
  vec3 col = mix(vec3(0.62, 0.66, 0.74), vec3(0.88, 0.9, 0.94), m);
  gl_FragColor = vec4(col * a, a);
}`;

// Rays fan out from uOrigin (uv, y up). The story scenes keep a source above the
// top edge whose rays fall in a downward cone (uCone 1) with a halo mid-screen;
// a page with its own light source (the sun of the welcome picture, createScreenFx
// option `light`) puts origin and halo on it and lets the rays spread all round.
const RAYS_FRAGMENT = /* glsl */ `
uniform float uTime;
uniform float uRays;
uniform float uAspect;
uniform vec2 uOrigin;
uniform vec2 uHalo;
uniform float uCone;
uniform float uHaloScale;
varying vec2 vUv;
void main() {
  vec2 d = vec2((vUv.x - uOrigin.x) * uAspect, vUv.y - uOrigin.y);
  float ang = atan(d.x, -d.y);
  float dist = length(d);
  float r = pow(0.5 + 0.5 * sin(ang * 13.0 + uTime * 0.35), 6.0);
  r += pow(0.5 + 0.5 * sin(ang * 7.3 - uTime * 0.22 + 1.7), 8.0) * 0.8;
  r += pow(0.5 + 0.5 * sin(ang * 21.0 + uTime * 0.5 + 3.1), 10.0) * 0.5;
  float fall = smoothstep(1.55, 0.2, dist) * smoothstep(0.0, 0.25, dist);
  float cone = mix(1.0, smoothstep(0.95, 0.1, abs(ang)), uCone);
  // Around a picture's own light (uCone 0) the rays mostly rise and spread sideways;
  // those falling across the land below it stay faint
  float rise = mix(0.3, 1.0, smoothstep(-0.7, 0.25, d.y / max(dist, 0.001)));
  cone *= mix(rise, 1.0, uCone);
  float halo = exp(-pow(length(vec2((vUv.x - uHalo.x) * uAspect, vUv.y - uHalo.y)) * uHaloScale, 2.0)) * 0.22;
  float i = (r * fall * cone * 0.3 + halo) * uRays;
  gl_FragColor = vec4(vec3(1.0, 0.86, 0.58) * i, 0.0);
}`;

const SPARK_VERTEX = /* glsl */ `
attribute vec4 aSeed;   // x: column, y: phase, z: speed, w: size
uniform float uTime;
uniform float uSpark;
uniform float uPixel;
uniform vec2 uShift;
varying float vAlpha;
void main() {
  float life = fract(aSeed.y + uTime * aSeed.z * 0.04);
  float x = aSeed.x * 2.2 - 1.1 + sin(uTime * 0.6 * aSeed.z + aSeed.y * 30.0) * 0.04;
  float y = -1.1 + life * 2.3;
  gl_Position = vec4(vec2(x, y) + uShift * (0.6 + aSeed.w), 0.0, 1.0);
  float twinkle = 0.55 + 0.45 * sin(uTime * (1.5 + aSeed.w * 2.0) + aSeed.y * 50.0);
  vAlpha = smoothstep(0.0, 0.15, life) * smoothstep(1.0, 0.7, life) * twinkle * uSpark;
  gl_PointSize = (3.0 + aSeed.w * 7.0) * uPixel;
}`;

const SPARK_FRAGMENT = /* glsl */ `
varying float vAlpha;
void main() {
  vec2 c = gl_PointCoord - 0.5;
  float g = exp(-dot(c, c) * 18.0);
  vec3 col = mix(vec3(1.0, 0.74, 0.38), vec3(1.0, 0.96, 0.82), g);
  gl_FragColor = vec4(col * g * vAlpha, 0.0);
}`;

const BRIGHT_FRAGMENT = /* glsl */ `
uniform sampler2D tInput;
varying vec2 vUv;
void main() {
  vec3 c = texture2D(tInput, vUv).rgb;
  float l = max(c.r, max(c.g, c.b));
  gl_FragColor = vec4(c * smoothstep(0.42, 0.8, l), 1.0);
}`;

const BLUR_FRAGMENT = /* glsl */ `
uniform sampler2D tInput;
uniform vec2 uStep;
varying vec2 vUv;
void main() {
  vec3 s = texture2D(tInput, vUv).rgb * 0.2270;
  s += (texture2D(tInput, vUv + uStep * 1.3846).rgb + texture2D(tInput, vUv - uStep * 1.3846).rgb) * 0.3162;
  s += (texture2D(tInput, vUv + uStep * 3.2308).rgb + texture2D(tInput, vUv - uStep * 3.2308).rgb) * 0.0703;
  gl_FragColor = vec4(s, 1.0);
}`;

const OUTPUT_FRAGMENT = /* glsl */ `
uniform sampler2D tScene;
uniform sampler2D tBloom;
uniform float uBloom;
varying vec2 vUv;
void main() {
  vec4 s = texture2D(tScene, vUv);
  vec3 c = s.rgb + texture2D(tBloom, vUv).rgb * uBloom;
  c = vec3(1.0) - exp(-c * 1.2);  // soft shoulder: bright light saturates instead of clipping to white
  float a = clamp(max(s.a, max(c.r, max(c.g, c.b))), 0.0, 1.0);
  gl_FragColor = vec4(min(c, vec3(a)), a);
}`;

/**
 * Keyframed value: `track(now, [[ms, value], …])` starts from the current
 * value at `now`; `at(now)` eases between the keys (smoothstep).
 */
function channel(initial) {
  let keys = [[0, initial]];
  let start = 0;
  let value = initial;

  return {
    get value() { return value; },
    track(now, list) {
      start = now;
      keys = [[0, value], ...list];
    },
    at(now) {
      const t = now - start;
      let i = 0;
      while (i < keys.length - 1 && keys[i + 1][0] <= t) i += 1;
      if (i >= keys.length - 1) {
        value = keys[keys.length - 1][1];
      } else {
        const [t0, v0] = keys[i];
        const [t1, v1] = keys[i + 1];
        const k = Math.min(1, Math.max(0, (t - t0) / Math.max(1, t1 - t0)));
        value = v0 + (v1 - v0) * k * k * (3 - 2 * k);
      }
      return value;
    },
  };
}

/**
 * @param {HTMLElement} root narrated scene with `data-fx3d`
 * @param {{ threeUrl: string, onFail?: () => void, light?: { x: number, y: number } | null, quality?: number }} options
 *        light: point of the stage in 0…1 from the left and from the top
 * @returns {Promise<{ setEffect: (name: string | null) => void, pause: () => void,
 *                     resume: () => void, destroy: () => void } | null>}
 */
export async function createScreenFx(root, { threeUrl, onFail = null, light = null, quality: startQuality = 0 }) {
  // three.js r163+ needs WebGL 2: without it, skip the 2 MB download and three's console errors
  const probe = document.createElement('canvas').getContext('webgl2');
  if (!probe) return null;
  probe.getExtension('WEBGL_lose_context')?.loseContext();

  let THREE;
  try {
    THREE = await import(threeUrl);
  } catch {
    return null;
  }

  const canvas = document.createElement('canvas');
  canvas.className = 'screen-fx';
  canvas.setAttribute('aria-hidden', 'true');

  let renderer;
  try {
    renderer = new THREE.WebGLRenderer({ canvas, alpha: true, premultipliedAlpha: true, antialias: false, powerPreference: 'high-performance' });
  } catch {
    return null; // no WebGL 2: the CSS effects stay
  }
  renderer.setClearColor(0x000000, 0);
  renderer.autoClear = false;
  // three.js logs a shader that does not compile instead of throwing
  let shaderError = false;
  renderer.debug.onShaderError = () => { shaderError = true; };

  const fxLayer = root.querySelector(':scope > [data-narrator-fx]');
  root.insertBefore(canvas, fxLayer ? fxLayer.nextSibling : root.firstChild);

  const camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);
  const plane = new THREE.PlaneGeometry(2, 2);
  const shared = { uTime: { value: 0 }, uAspect: { value: 16 / 9 } };

  const premultiplied = {
    transparent: true, depthTest: false, depthWrite: false, premultipliedAlpha: true,
    blending: THREE.CustomBlending,
    blendSrc: THREE.OneFactor, blendDst: THREE.OneMinusSrcAlphaFactor,
    blendSrcAlpha: THREE.OneFactor, blendDstAlpha: THREE.OneMinusSrcAlphaFactor,
  };
  const additive = {
    transparent: true, depthTest: false, depthWrite: false, premultipliedAlpha: true,
    blending: THREE.CustomBlending,
    blendSrc: THREE.OneFactor, blendDst: THREE.OneFactor,
    blendSrcAlpha: THREE.ZeroFactor, blendDstAlpha: THREE.OneFactor,
  };

  const fogUniforms = { ...shared, uFog: { value: BASE.fog }, uShift: { value: new THREE.Vector2() } };
  const raysUniforms = {
    ...shared,
    uRays: { value: BASE.rays },
    uOrigin: { value: light ? new THREE.Vector2(light.x, 1 - light.y) : new THREE.Vector2(0.5, 1.18) },
    uHalo: { value: light ? new THREE.Vector2(light.x, 1 - light.y) : new THREE.Vector2(0.5, 0.58) },
    uCone: { value: light ? 0 : 1 },
    uHaloScale: { value: light ? 3.2 : 2.2 },
  };
  const sparkUniforms = { uTime: shared.uTime, uSpark: { value: BASE.spark }, uPixel: { value: 1 }, uShift: { value: new THREE.Vector2() } };

  const scene = new THREE.Scene();
  const fog = new THREE.Mesh(plane, new THREE.ShaderMaterial({ uniforms: fogUniforms, vertexShader: QUAD_VERTEX, fragmentShader: FOG_FRAGMENT, ...premultiplied }));
  const rays = new THREE.Mesh(plane, new THREE.ShaderMaterial({ uniforms: raysUniforms, vertexShader: QUAD_VERTEX, fragmentShader: RAYS_FRAGMENT, ...additive }));

  const seeds = new Float32Array(SPARKS * 4);
  for (let i = 0; i < seeds.length; i += 4) {
    seeds[i] = Math.random();
    seeds[i + 1] = Math.random();
    seeds[i + 2] = 0.5 + Math.random() * 1.2;
    seeds[i + 3] = Math.random() ** 2;
  }
  const sparkGeometry = new THREE.BufferGeometry();
  sparkGeometry.setAttribute('position', new THREE.BufferAttribute(new Float32Array(SPARKS * 3), 3));
  sparkGeometry.setAttribute('aSeed', new THREE.BufferAttribute(seeds, 4));
  const sparks = new THREE.Points(sparkGeometry, new THREE.ShaderMaterial({ uniforms: sparkUniforms, vertexShader: SPARK_VERTEX, fragmentShader: SPARK_FRAGMENT, ...additive }));

  for (const [order, mesh] of [fog, rays, sparks].entries()) {
    mesh.frustumCulled = false;
    mesh.renderOrder = order;
    scene.add(mesh);
  }

  // Post-processing: one quad, its material swapped per pass
  const target = () => new THREE.WebGLRenderTarget(1, 1, { depthBuffer: false });
  const sceneTarget = target();
  const bloomA = target();
  const bloomB = target();
  const pass = (fragmentShader, uniforms) => new THREE.ShaderMaterial({
    uniforms, vertexShader: QUAD_VERTEX, fragmentShader, depthTest: false, depthWrite: false, blending: THREE.NoBlending,
  });
  const bright = pass(BRIGHT_FRAGMENT, { tInput: { value: sceneTarget.texture } });
  const blur = pass(BLUR_FRAGMENT, { tInput: { value: null }, uStep: { value: new THREE.Vector2() } });
  const output = pass(OUTPUT_FRAGMENT, { tScene: { value: sceneTarget.texture }, tBloom: { value: bloomA.texture }, uBloom: { value: 0.9 } });
  const post = new THREE.Scene();
  const quad = new THREE.Mesh(plane, output);
  quad.frustumCulled = false;
  post.add(quad);

  const draw = (material, into) => {
    quad.material = material;
    renderer.setRenderTarget(into);
    renderer.clear();
    renderer.render(post, camera);
  };
  const blurInto = (from, into, x, y) => {
    blur.uniforms.tInput.value = from.texture;
    blur.uniforms.uStep.value.set(x / from.width, y / from.height);
    draw(blur, into);
  };

  // ------------------------------------------------------------ size and quality

  let quality = Math.min(Math.max(0, startQuality), QUALITY.length - 1);
  let width = 0;
  let height = 0;

  function resize() {
    const box = canvas.getBoundingClientRect();
    const ratio = Math.min(window.devicePixelRatio || 1, QUALITY[quality].ratio);
    const w = Math.max(2, Math.round(box.width * ratio));
    const h = Math.max(2, Math.round(box.height * ratio));
    if (w === width && h === height) return;
    width = w;
    height = h;
    renderer.setPixelRatio(1);
    renderer.setSize(w, h, false);
    sceneTarget.setSize(w, h);
    bloomA.setSize(Math.max(1, w >> 2), Math.max(1, h >> 2));
    bloomB.setSize(Math.max(1, w >> 2), Math.max(1, h >> 2));
    shared.uAspect.value = w / h;
    sparkUniforms.uPixel.value = h / 720;
  }
  const resizer = new ResizeObserver(() => resize());
  resizer.observe(canvas);

  // ------------------------------------------------------------------ effects

  const levels = { fog: channel(BASE.fog), rays: channel(BASE.rays), spark: channel(BASE.spark) };

  function setEffect(name) {
    const now = performance.now();
    switch (name) {
      case 'fog':
        levels.fog.track(now, [[3200, 0.85]]);
        levels.spark.track(now, [[2400, 0.3]]);
        levels.rays.track(now, [[1200, 0]]);
        break;
      case 'fog-lift':
        levels.fog.track(now, [[300, 0.85], [3100, 0.05], [6000, BASE.fog]]);
        levels.spark.track(now, [[1800, 1.2]]);
        levels.rays.track(now, [[1400, 0.35], [4000, 0.2]]);
        break;
      case 'glow':
        levels.rays.track(now, [[900, 1], [1560, 0.55], [2600, 0.8]]);
        levels.spark.track(now, [[1200, 1.25]]);
        levels.fog.track(now, [[1600, BASE.fog * 0.5]]);
        break;
      case 'flash':
        levels.spark.track(now, [[120, 2.4], [1400, BASE.spark]]);
        levels.rays.track(now, [[120, 0.7], [1200, BASE.rays]]);
        break;
      case 'dim':
        levels.spark.track(now, [[1600, 0.3]]);
        levels.rays.track(now, [[1200, 0]]);
        break;
      case 'sun':
        levels.rays.track(now, [[4000, 0.6]]);
        levels.spark.track(now, [[3000, 0.9]]);
        levels.fog.track(now, [[3000, BASE.fog]]);
        break;
      default:
        levels.fog.track(now, [[1800, BASE.fog]]);
        levels.rays.track(now, [[1400, BASE.rays]]);
        levels.spark.track(now, [[1400, BASE.spark]]);
    }
  }

  // --------------------------------------------------------------------- loop

  let frame = 0;
  let last = 0;
  let slowSince = 0;
  let running = false;
  let failed = false;
  const start = performance.now();

  function render(now) {
    shared.uTime.value = (now - start) / 1000;
    fogUniforms.uFog.value = levels.fog.at(now);
    raysUniforms.uRays.value = levels.rays.at(now);
    sparkUniforms.uSpark.value = levels.spark.at(now);

    const { x, y } = parallaxValue();
    fogUniforms.uShift.value.set(x * 0.08, -y * 0.05);
    sparkUniforms.uShift.value.set(-x * 0.045, y * 0.04);

    rays.visible = raysUniforms.uRays.value > 0.002;

    renderer.setRenderTarget(sceneTarget);
    renderer.clear();
    renderer.render(scene, camera);

    const bloomOn = QUALITY[quality].bloom;
    if (bloomOn) {
      draw(bright, bloomA);
      blurInto(bloomA, bloomB, 1, 0);
      blurInto(bloomB, bloomA, 0, 1);
      blurInto(bloomA, bloomB, 2, 0);
      blurInto(bloomB, bloomA, 0, 2);
    }
    output.uniforms.uBloom.value = bloomOn ? 0.9 : 0;
    draw(output, null);
  }

  /** Two quality steps for frames that stay slow, then the CSS effects. */
  function watch(now) {
    const dt = now - last;
    last = now;
    if (dt > 250 || now - start < 3000) { slowSince = 0; return; } // tab switch or warm-up
    if (dt <= SLOW_MS) { slowSince = 0; return; }
    slowSince ||= now;
    if (now - slowSince < SLOW_FOR_MS) return;
    slowSince = 0;
    if (quality < QUALITY.length - 1) {
      quality += 1;
      width = 0;
      resize();
    } else {
      fail();
    }
  }

  function tick(now) {
    if (!running) return;
    frame = requestAnimationFrame(tick);
    watch(now);
    render(now);
  }

  function pause() {
    running = false;
    cancelAnimationFrame(frame);
  }

  function resume() {
    if (running || failed) return;
    running = true;
    last = performance.now();
    resize();
    frame = requestAnimationFrame(tick);
  }

  function destroy() {
    pause();
    resizer.disconnect();
    for (const item of [plane, sparkGeometry, fog.material, rays.material, sparks.material, bright, blur, output, sceneTarget, bloomA, bloomB]) item.dispose();
    renderer.dispose();
    canvas.remove();
    root.classList.remove('has-fx3d');
  }

  function fail() {
    if (failed) return;
    failed = true;
    destroy();
    onFail?.();
  }

  canvas.addEventListener('webglcontextlost', (event) => {
    event.preventDefault();
    fail();
  });

  // One frame before the class: a shader that does not compile fails here, not on screen
  try {
    resize();
    render(performance.now());
  } catch {
    shaderError = true;
  }
  if (shaderError) {
    failed = true;
    destroy();
    return null;
  }
  root.classList.add('has-fx3d');

  return { setEffect, pause, resume, destroy };
}
