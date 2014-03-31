<?php

// web/index.php
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

/**
 * [makeHtml description]
 * 
 * @param array $sheet Sheet object
 *
 * @return string Sheet text with placeholders replaced.
 */
function makeHtml($sheet) {

    $element = 'span';

    $values = implode('##', array_map(function($collection){
        return implode('|', $collection);
    }, $sheet['values']));

    return preg_replace(
        '/[\.]{4,1000}/',
        '<'.$element.' class="hiatus-textatrous-placeholder" data-values="' . str_replace('"', '\"', $values) . '">' . '....' . '</'.$element.'>',
        $sheet['body']
    );
}

// ... config

$app['debug'] = true;

$sheets = array(
    'test' => array(
        'date'      => '2011-03-29',
        'title'     => 'Using Silex',
        'slug'      => 'test',
        'body'      => 'Un .... texte avec des ...... trous.',
        'values'    => array(
            array( 'super', 'jolis'  ),
            array( 'long',  'grands' ),
        )
    ),
);

// ... definitions

$app->get('/', function () use ($app, $sheets) {
    return $app['twig']->render('index.twig', array(
        'sheets' => $sheets
    ));
});

$app->get('/s/{sheetId}', function ($sheetId) use ($app, $sheets) {

    $sheet = $sheets[$sheetId];
    $sheet['body'] = makeHtml($sheet);

    return $app['twig']->render('sheet.twig', array(
        'sheets' => $sheets,
        'sheet' => $sheet
    ));
});

$app->run();
