<?php return array(
    'root' => array(
        'pretty_version' => 'dev-develop',
        'version' => 'dev-develop',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => 'f51caf13815260a44c3100161358790e5b9a8520',
        'name' => 'pauple/tablesome',
        'dev' => false,
    ),
    'versions' => array(
        'composer/installers' => array(
            'pretty_version' => 'v1.12.0',
            'version' => '1.12.0.0',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/./installers',
            'aliases' => array(),
            'reference' => 'd20a64ed3c94748397ff5973488761b22f6d3f19',
            'dev_requirement' => false,
        ),
        'pauple/pluginator' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'type' => 'pauple-library',
            'install_path' => __DIR__ . '/../pauple/pluginator',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'reference' => '7c80053274eee5235b43cb4d54f5662835439b51',
            'dev_requirement' => false,
        ),
        'pauple/tablesome' => array(
            'pretty_version' => 'dev-develop',
            'version' => 'dev-develop',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => 'f51caf13815260a44c3100161358790e5b9a8520',
            'dev_requirement' => false,
        ),
        'roundcube/plugin-installer' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'shama/baton' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
    ),
);
