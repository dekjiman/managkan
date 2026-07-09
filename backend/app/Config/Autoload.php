<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

class Autoload extends AutoloadConfig
{
    public $psr4 = [
        APP_NAMESPACE => APPPATH,
        'Module'      => ROOTPATH . 'Module',
        'Sites'       => ROOTPATH . 'Sites',
    ];

    public $classmap = [];

    public $files = [];

    public $helpers = ['public_id_helper'];
}
