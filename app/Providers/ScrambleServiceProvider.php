<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\ServiceProvider;

class ScrambleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Scramble::configure(function (\Dedoc\Scramble\Configuration\Configuration $config) {
            $config->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer', 'JWT')
                );
            });
        });
    }
}
