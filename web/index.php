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

    $rndVersion = array_rand($sheet['values']);
    $rndVersionValues = $sheet['values'][$rndVersion];

    $i = 0;
    return'<div class="sheet-obj" data-sheet-id="' . $sheet['id'] . '" data-h-index="' . $rndVersion . '">'
        . preg_replace_callback(
            '/[\$](\d+)/',
            function ($matches) use ($values, $rndVersionValues, $element, &$i) {
                return '<'.$element.' class="hiatus-placeholder" data-h-index="' . $matches[1] . '">' . $rndVersionValues[$matches[1]] . '</'.$element.'>';
            },
            $sheet['body']
        )
        . '</div>';
}

// ... config

$app['debug'] = true;

$sheets = array(
    'test' => array(
        'id'        => 12345,
        'date'      => '2011-03-29',
        'title'     => 'Using Silex',
        'slug'      => 'test',
        'body'      => 'Un $0 texte avec des $1 trous.',
        'values'    => array(
            array( 'super', 'jolis'  ),
            array( 'long',  'grands' ),
            array( 'petit', 'espèce de' ),
        )
    ),
    'test2' => array(
        'id'        => 12346,
        'date'      => '2011-03-25',
        'title'     => 'Another example',
        'slug'      => 'another-example',
        'body'      => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In semper fermentum fermentum. Nam lacinia, risus ut malesuada dapibus, ligula erat semper sem, a faucibus elit tellus id ante. Mauris aliquam tincidunt adipiscing. Etiam a ligula a tortor pellentesque aliquet. Pellentesque id imperdiet ipsum. Maecenas sit amet leo turpis. Curabitur ac vulputate neque.

Phasellus auctor lobortis semper. Donec sit amet dolor $0, aliquam metus eget, luctus lorem. In ac urna ante. Duis et ante elit. Donec suscipit erat tortor, vitae venenatis ligula semper nec. Vivamus venenatis ligula et odio scelerisque, non volutpat purus porta. Donec consequat varius dui sollicitudin ultricies. Nullam ac ipsum eros. Nam pulvinar velit sit amet lacus bibendum, a gravida massa sagittis.

Fusce sit amet molestie sapien, eu fringilla dui. Proin non interdum neque. Donec $1 nisl turpis. Phasellus blandit diam nulla, id laoreet diam mollis quis. Fusce vulputate vulputate urna vitae blandit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent mollis sem eu lorem interdum vulputate. Nunc at nunc vel ante suscipit viverra. Duis augue sem, mattis porttitor risus eget, consequat aliquam sem. Proin tempus fermentum turpis, non cursus sapien blandit malesuada. Mauris convallis malesuada arcu nec porttitor. Nam sed nunc vel libero luctus tincidunt. Morbi lacinia tempor nulla, ut volutpat enim eleifend eget. Maecenas nec risus nulla. Cras nec auctor nibh.

Sed iaculis nec nibh eget aliquet. Integer molestie turpis ante. Etiam congue dictum arcu, quis viverra lorem. Donec sed varius $3. Maecenas libero neque, tristique a elit eget, mattis sodales est. Nulla malesuada commodo est luctus molestie. Vestibulum in mi sapien. Curabitur ligula tortor, elementum ac turpis in, sollicitudin eleifend leo.',
        'values'    => array(
            array( 'super', 'jolis', 'ploptest'  ),
            array( 'long',  'grands', 'zefrttyh' ),
        )
    ),
);

// ... definitions

$app->get('/', function () use ($app, $sheets) {
    return $app['twig']->render('index.twig', array(
        'sheets' => $sheets,
        'sheet'  => false
    ));
});

// $app->get('/s/{sheetId}', function ($sheetId) use ($app, $sheets) {

//     $sheet = $sheets[$sheetId];

//     $rndSheet = array_rand($sheet['values']);

//     return $app['twig']->render('sheet.twig', array(
//         'sheets'  => $sheets,
//         'sheet'   => $sheet
//     ));
// });

$app->get('/sheets', function () use ($app, $sheets) {
    header('Content-type: text/json');
    return json_encode(array_values($sheets));
});

$app->run();
