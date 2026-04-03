<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/api/docs.json' => [[['_route' => 'payment_api_doc_json', '_controller' => 'nelmio_api_doc.controller.swagger'], null, ['GET' => 0], null, false, false, null]],
        '/api/docs' => [[['_route' => 'payment_api_doc_ui', '_controller' => 'nelmio_api_doc.controller.swagger_ui'], null, ['GET' => 0], null, false, false, null]],
        '/payment/console' => [[['_route' => 'payment_console', '_controller' => 'App\\Controller\\PaymentConsoleController::console'], null, ['GET' => 0], null, false, false, null]],
        '/payment/console/create' => [[['_route' => 'payment_console_create', '_controller' => 'App\\Controller\\PaymentConsoleController::create'], null, ['POST' => 0], null, false, false, null]],
        '/payment/console/start' => [[['_route' => 'payment_console_start', '_controller' => 'App\\Controller\\PaymentConsoleController::start'], null, ['POST' => 0], null, false, false, null]],
        '/payment/console/finalize' => [[['_route' => 'payment_console_finalize', '_controller' => 'App\\Controller\\PaymentConsoleController::finalize'], null, ['POST' => 0], null, false, false, null]],
        '/payment/console/refund' => [[['_route' => 'payment_console_refund', '_controller' => 'App\\Controller\\PaymentConsoleController::refund'], null, ['POST' => 0], null, false, false, null]],
        '/api/payments' => [[['_route' => 'payment_create', '_controller' => 'App\\Controller\\PaymentCreateController::create'], null, ['POST' => 0], null, false, false, null]],
        '/payment/start' => [[['_route' => 'payment_start', '_controller' => 'App\\Controller\\StartController::start'], null, ['POST' => 0], null, false, false, null]],
        '/metrics' => [[['_route' => 'payment_metrics', '_controller' => 'App\\Controller\\MetricController::metrics'], null, ['GET' => 0], null, false, false, null]],
        '/payment/dlq' => [[['_route' => 'payment_dlq_list', '_controller' => 'App\\Controller\\DlqController::list'], null, ['GET' => 0], null, false, false, null]],
        '/status' => [[['_route' => 'payment_status', '_controller' => 'App\\Controller\\StatusController::status'], null, ['GET' => 0], null, false, false, null]],
        '/webhook/stripe' => [[['_route' => 'payment_webhook_stripe', '_controller' => 'App\\Controller\\Webhook\\StripeWebhookController::__invoke'], null, ['POST' => 0], null, false, false, null]],
        '/webhook/paypal' => [[['_route' => 'payment_webhook_paypal', '_controller' => 'App\\Controller\\Webhook\\PayPalWebhookController::__invoke'], null, ['POST' => 0], null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/api/payments/([^/]++)(?'
                    .'|(*:32)'
                    .'|/refund(*:46)'
                .')'
                .'|/payment/(?'
                    .'|finalize/([^/]++)(*:83)'
                    .'|dlq/replay/([^/]++)(*:109)'
                    .'|webhook/([^/]++)(*:133)'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        32 => [[['_route' => 'payment_read', '_controller' => 'App\\Controller\\PaymentReadController::read'], ['id'], ['GET' => 0], null, false, true, null]],
        46 => [[['_route' => 'payment_refund', '_controller' => 'App\\Controller\\PaymentRefundController::refund'], ['id'], ['POST' => 0], null, false, false, null]],
        83 => [[['_route' => 'payment_finalize', '_controller' => 'App\\Controller\\FinalizeController::finalize'], ['id'], ['POST' => 0], null, false, true, null]],
        109 => [[['_route' => 'payment_dlq_replay', '_controller' => 'App\\Controller\\DlqController::replay'], ['id'], ['POST' => 0], null, false, true, null]],
        133 => [
            [['_route' => 'payment_webhook', '_controller' => 'App\\Controller\\WebhookController::webhook'], ['provider'], ['POST' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
