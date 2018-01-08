<?php
/** Freesewing\Services\CompareService class */
namespace Freesewing\Services;

use Freesewing\Context;

/**
 * Handles the compare service, which compare the user pattern with the sample patterns.
 *
 * @author    Joost De Cock <joost@decock.org>
 * @copyright 2016 Joost De Cock
 * @license   http://opensource.org/licenses/GPL-3.0 GNU General Public License, Version 3
 */
class CompareService extends DraftService
{

    /**
     * Returns the name of the service
     *
     * This is used to load the default theme for the service when no theme is specified
     *
     * @see Context::loadTheme()
     *
     * @return string
     */
    public function getServiceName()
    {
        return 'compare';
    }

    /**
     * Samples a pattern
     *
     * This samples a pattern, sets the response and sends it
     * Essentially, it takes care of the entire remainder of the request
     *
     * @param \Freesewing\Context $context
     */
    public function run(Context $context)
    {
        $context->addUnits();
        $context->addPattern();

        if ($context->getChannel()
                ->isValidRequest($context) === true
        ) :

            $context->addTranslator();
            $context->getPattern()
                ->setTranslator($context->getTranslator());

            $context->getPattern()
                ->setPartMargin($context->getTheme()->config['settings']['partMargin']);
            $context->getPattern()->setVersion($context->getConfig()['version']);
            $context->getTheme()
                ->setOptions($context->getRequest());

            $context->addMeasurementsSampler();
            $context->getMeasurementsSampler()
                ->setPattern($context->getPattern());

            // add options like 'chestEase' to all compare-models
            $context->getPattern()->addOptions(
                $context->getChannel()->standardizePatternOptions(
                    $context->getRequest(),
                    $context->getPattern()
                )
            );

            $context->getMeasurementsSampler()
                ->setModelConfig($context->getPattern()
                    ->getSamplerModelConfig());
            $context->getMeasurementsSampler()
                ->loadPatternModels($context->getRequest()
                    ->getData('samplerGroup'));

            // add the user-measurements to the sampler
            $context->getMeasurementsSampler()->addPatternModel(
                $context->getChannel()->standardizeModelMeasurements(
                    $context->getRequest(),
                    $context->getPattern()
                ),
                'compareModel'
            );

            $context->setPattern(
                $context->getMeasurementsSampler()->sampleMeasurements(
                    $context->getTheme()
                )
            );

            $context->addSvgDocument();
            $context->addRenderbot();
            $this->svgRender($context);
            $context->setResponse($context->getTheme()
                ->themeResponse($context));
        else: // channel->isValidRequest() !== true
            $context->getChannel()
                ->handleInvalidRequest($context);
        endif;

        // Don't send response without approval from the channel
        if($context->getChannel()->isValidResponse($context)) {
            $context->getResponse()->send();
        } else {
            $context->getChannel()->handleInvalidResponse($context);
        }

        $context->cleanUp();
    }
}
