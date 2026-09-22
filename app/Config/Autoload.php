<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

class Autoload extends AutoloadConfig
{
    /** @var array<string, list<string>|string> */
    public $psr4 = [
        APP_NAMESPACE => APPPATH,
    ];

    /** @var array<string, string> */
    public $classmap = [];

    /** @var list<string> */
    public $files = [];

    /**
     * Dimuat global karena dipakai filter, session handler, event,
     * dan halaman error — yang berjalan sebelum/di luar BaseController.
     *
     * @var list<string>
     */
    public $helpers = ['url', 'gelita', 'content'];
}
