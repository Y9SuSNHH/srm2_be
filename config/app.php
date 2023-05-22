<?php

return [
    'secret_path' => storage_path('app/private/secret.key'),

    'domains' => [
        'api' => [
            'middleware' => 'cors:except:.*/download',
        ],
        'academic-affairs-officer' => [
            'middleware' => ['cors:except:.*/download', 'auth:api', 'reg_repo:academic-affairs-officer'],
            'as' => 'academicAffairsOfficer',
        ],
        'training-programme' => [
            'middleware' => ['cors:except:.*/download/template', 'auth:api', 'reg_repo:training-programme'],
            'as' => 'trainingProgramme',
        ],
        'major-object-map' => [
            'middleware' => ['cors', 'auth:api', 'reg_repo:major-object-map'],
            'as' => 'majorObjectMap',
        ],
        'learning-module' => [
            'middleware' => ['cors', 'auth:api', 'reg_repo:learning-module'],
            'as' => 'learningModule',
        ],
        'student' => [
            'middleware' => ['cors:except:.*/download,export-g110', 'auth:api','reg_repo:student'],
            'as' => 'student',
        ],
        'study-plan' => [
            'middleware' => ['cors', 'auth:api', 'reg_repo:study-plan'],
            'as' => 'studyPlan',
        ],
        'reports' => [
            'middleware' => ['cors:except:.*/export', 'auth:api', 'reg_repo:reports'],
            'as' => 'reports',
        ],
        'receivable' => [
            'prefix' => '',
            'middleware' => ['cors', 'auth:api','reg_repo:receivable'],
            'as' => 'receivable',
        ],
        'web' => [
            
        ],
        'contact' => [
            'middleware' => ['cors', 'auth:api','reg_repo:contact'],
            'as' => 'contact'
        ],
        'registration' => [
            'middleware' => ['cors:except:.*/export', 'auth:api','reg_repo:registration'],
            'as' => 'registration'
        ],
        'workflow' => [
            'middleware' => ['cors', 'auth:api','reg_repo:workflow'],
            'as' => 'workflow'
        ],
        'finance' => [
            'middleware' => ['cors:except:.*/download', 'auth:api','reg_repo:finance'],
            'as' => 'finance'
        ],
    ],

    'providers' => [
        App\Http\Domain\Web\Providers\RepositoryServiceProvider::class,
        App\Http\Domain\Api\Providers\RepositoryServiceProvider::class,
        App\Http\Domain\Common\Providers\RepositoryServiceProvider::class,
    ],
    'route_middleware' => [
        'auth' => App\Http\Middleware\Authenticate::class,
        'cors' => App\Http\Middleware\CorsMiddleware::class,
        'reg_repo' => App\Http\Middleware\RegisterRepositoryMiddleware::class,
    ],
];
