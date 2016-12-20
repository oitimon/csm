<?php

include_once "../vendor/autoload.php";

use Csm\CsmService;
use Csm\CsmIdent;

/**
 * Prepare params. We want default engine Filesystem, just set pathes.
 */
$params = [
    'drivers' => [
        'main' => [
            'type'         => \Csm\Driver\Filesystem::class,
            'resourcePath' => realpath(__DIR__ . '/public/resources'),
            'resourceUrl'  => '/resources'
        ]
    ]
];

/**
 * Prepare example content to play with.
 */
$exampleContent = file_get_contents('resources/man-1351317_640.png');

/**
 * Init service with params.
 */
$csmService = new CsmService($params);

/**
 * Create Identificator like User's avatar.
 */
$userId = 180532;
$ident = CsmIdent::create()
    ->addResourceName('users')
    ->addResourceName('profile')
    ->addNumeric($userId);

/**
 * Save content (User's avatar) to storage.
 * File will be saved to directory 'examples/public/resources/users/profile/18/05/32/'.
 */
$name = 'avatar.png';
$csmService->set($ident, $exampleContent, $name);

/**
 * Get URL for content.
 */
$url = 'http://myhost.app'.$csmService->getPreparedUrl($ident, $name);
echo "URL for avatar of User with ID $userId: $url\n";

/**
 * Delete file from storage.
 */
$csmService->delete($ident, $name);

/**
 * Show URL only file is exists.
 */
echo 'After deleting: ';
if ($csmService->isPresent($ident, $name)) {
    /**
     * Show URL to file if it is present.
     */
    $url = 'http://myhost.app'.$csmService->getPreparedUrl($ident, $name)."\n";
} else {
    /**
     * Show some stub if it is absent.
     */
    echo "http://myhost.app/userProfileAvatarStub.jpg\n";
}
