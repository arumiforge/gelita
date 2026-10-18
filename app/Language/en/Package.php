<?php

/**
 * Asset package screens (docs/rencana-paket-aset.md Stage 3): "Siapkan GELITA"
 * (`/siapkan`, students, with notes for the teacher) and "Periksa paket"
 * (`/periksa-paket`, teachers). Texts the scripts write while downloading or
 * checking live in Js.php (`package*`, `inspect*`).
 */
return [
    // Siapkan GELITA (students)
    'prepareTitle'      => 'Get GELITA ready',
    'prepareEyebrow'    => 'Only once on this device',
    'prepareLead'       => 'GELITA is saving its pictures and sounds on this device, in both languages. It only happens once, and then the game runs faster. Please wait until it is done!',
    'prepareTotal'      => 'All parts',
    'prepareParts'      => 'Parts being prepared',
    'prepareWaiting'    => 'Waiting',
    'groupKode'         => 'Game code',
    'groupInti'         => 'Core (every screen)',
    'groupRegion'       => '{0} region',
    'groupPenutup'      => 'Ending',
    'playOnline'        => 'Play online on this device',
    'teacherTitle'      => 'For teachers',
    'teacherInspect'    => 'Check the package on this device',
    'teacherLab'        => 'Lab devices that wipe their data at every logout cannot keep the package. On such a device, GELITA may be played online.',
    'prepareInstallIos' => 'On iPad, add GELITA to the Home Screen so its pictures and sounds stay saved: tap Share, then choose Add to Home Screen.',
    'prepareNoScript'   => 'This device does not run JavaScript, so the package cannot be saved. GELITA can still be played:',

    // Periksa paket (teachers)
    'inspectTitle'      => 'Check the package',
    'inspectEyebrow'    => 'For teachers, before class',
    'inspectLead'       => 'Check the asset package on this device before the students arrive. Every file is checked again, and missing or damaged ones are downloaded again.',
    'inspectState'      => 'State',
    'inspectDevice'     => 'Package on this device',
    'inspectServer'     => 'Package for students',
    'inspectStorage'    => 'Storage',
    'inspectPersisted'  => 'Keep permanently',
    'inspectVerify'     => 'Check every file',
    'inspectRepair'     => 'Download what is missing',
    'inspectPersist'    => 'Keep permanently',
    'inspectClear'      => 'Remove the package from this device',
    'inspectNoScript'   => 'This page needs JavaScript.',
];
