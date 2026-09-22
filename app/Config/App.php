<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    public string $baseURL = 'http://localhost:8080/';

    /** @var list<string> */
    public array $allowedHostnames = [];

    public string $indexPage = '';

    public string $uriProtocol = 'REQUEST_URI';

    public string $permittedURIChars = 'a-z 0-9~%.:_\-';

    public string $defaultLocale = 'id';

    /**
     * Locale ditentukan pilihan eksplisit peserta (tombol ID/EN) dan disimpan
     * di session / game_sessions.locale — bukan ditebak dari header browser.
     */
    public bool $negotiateLocale = false;

    /** @var list<string> */
    public array $supportedLocales = ['id', 'en'];

    public string $appTimezone = 'Asia/Jakarta';

    public string $charset = 'UTF-8';

    public bool $forceGlobalSecureRequests = false;

    /** @var array<string, string> */
    public array $proxyIPs = [];

    public bool $CSPEnabled = false;
}
