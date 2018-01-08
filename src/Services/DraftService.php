<?php
/** Freesewing\Services\DraftService class */
namespace Freesewing\Services;

use Freesewing\Utils;

/**
 * Handles the draft service, which drafts patterns.
 *
 * @author    Joost De Cock <joost@decock.org>
 * @copyright 2016 Joost De Cock
 * @license   http://opensource.org/licenses/GPL-3.0 GNU General Public License, Version 3
 */
class DraftService extends Service
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
        return 'draft';
    }

    /**
     * Drafts a pattern
     *
     * This drafts a pattern, sets the response and sends it
     * Essentially, it takes care of the entire remainder of the request
     *
     * @param \Freesewing\Context
     */
    public function run(\Freesewing\Context $context)
    {
        $context->addUnits();
        $context->addPattern();

        if ($context->getChannel()->isValidRequest($context) === true) :
            $context->addModel();
            $context->getModel()->addMeasurements($context->getChannel()->standardizeModelMeasurements($context->getRequest(),
                $context->getPattern()));

            $context->getPattern()->addOptions($context->getChannel()->standardizePatternOptions($context->getRequest(), $context->getPattern()));

            $context->addTranslator();
            $context->getPattern()->setTranslator($context->getTranslator());

            $context->getTheme()->setOptions($context->getRequest());

            $context->getPattern()->setVersion($context->getConfig()['version']);
            $context->getPattern()->draft($context->getModel());
            $context->getPattern()->setPartMargin($context->getTheme()->config['settings']['partMargin']);

            $context->getTheme()->applyRenderMask($context->getPattern());
            $context->getPattern()->layout();

            $context->getTheme()->themePattern($context->getPattern());

            $context->addSvgDocument();
            $context->addRenderbot();
            $this->svgRender($context);

            $context->setResponse($context->getTheme()->themeResponse($context));

            /* Last minute replacements on the entire response body */
            $context->getResponse()->setBody($this->replace($context->getResponse()->getBody(), $context->getPattern()->getReplacements()));

        else :
            // channel->isValidRequest() !== true
            $context->getChannel()->handleInvalidRequest($context);
        endif;

        // Don't send response without approval from the channel
        if($context->getChannel()->isValidResponse($context)) {
            $context->getResponse()->send();
        } else {
            $context->getChannel()->handleInvalidResponse($context);
        }

        $context->cleanUp();
    }

    /**
     * Sets up the SVG document and calls the renderbot
     *
     * This add width and height attributes to the SVG and calls theme->themeSvg()
     * Then, it gets the renderbot to render the SVG body
     *
     * @param \Freesewing\Context
     */
    protected function svgRender(\Freesewing\Context $context)
    {
        // format specific themeing
        $context->getTheme()->themeSvg($context->getSvgDocument());
        
        // Size and viewbox
        $viewbox = $context->getRequest()->getData('viewbox');
        if ($viewbox !== null) {
            $viewbox = Utils::asScrubbedArray($viewbox, ',');
            $context->getSvgDocument()->svgAttributes->add('viewBox ="' . $viewbox[0] . ' ' . $viewbox[1] . ' ' . $viewbox[2] . ' ' . $viewbox[3] . '"');
        } else {
            $context->getSvgDocument()->svgAttributes->add('viewBox ="0 0 ' . $context->getPattern()->getWidth() . ' ' . $context->getPattern()->getHeight() . '"');
        }
        // Don't set size for themes with embedFluid options set to true, allows for responsive embedding
        if(!$context->getTheme()->getOption('embedFluid')) {
            $context->getSvgDocument()->svgAttributes->add('width ="' . $context->getPattern()->getWidth() . 'mm"');
            $context->getSvgDocument()->svgAttributes->add('height ="' . $context->getPattern()->getHeight() . 'mm"');
        }
        
        // Set freesewing specific attributes
        $context->getSvgDocument()->svgAttributes->add('freesewing:version ="' . $context->getPattern()->getVersion() . '"');
        $reqInfo = $context->getRequest()->getInfo();
        if(isset($reqInfo['host'])) $context->getSvgDocument()->svgAttributes->add('freesewing:origin ="' . $reqInfo['host'] . '"');
        else $context->getSvgDocument()->svgAttributes->add('freesewing:origin ="no_host_set"');

        // render SVG
        $context->getSvgDocument()->setSvgBody($context->getRenderbot()->render($context->getPattern()));
    }

    /**
     * Last minute replacements in the SVG
     *
     * Sometimes you want to add something to the SVG that is not available until later
     * Or, more commonly, you want to include something in defs or CSS and have it adapt to
     * whatever is going on in the pattern
     * For that, you can register a replacement in the pattern, and it will be handled here
     *
     * @param string svg
     * @param array  replacements
     *
     * @see \Freesewing\Patterns\Core\Pattern::replace()
     * @return string
     */
    private function replace($svg, $replacements)
    {
        if (is_array($replacements)) {
            $svg = str_replace(array_keys($replacements), array_values($replacements), $svg);
        }

        return $svg;
    }
}
