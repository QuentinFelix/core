<?php
/** Freesewing\OptionsSampler class */
namespace Freesewing;

/**
 * Samples a pattern for a specific option
 *
 * This takes an option and samples it for different values.
 * By default, it creates 11 steps between the minimum and maximum
 * value of an option.
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2016 Joost De Cock
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, Version 3
 */
class OptionsSampler extends Sampler
{
    /**
     * Loads the measurements of the default model.
     *
     * @return array|null Array of measurements or null if we can't read the config file
     *
     * @throws InvalidArgumentException if the config file cannot be read
     */
    public function loadModelMeasurements()
    {
        return $this->pattern->config['measurements'];
    }

    /**
     * Loads the option we are sampling from the pattern config.
     *
     * @param string $option The option to load
     *
     * @return array The configuration of the option
     *
     * @throws InvalidArgumentException if the config file cannot be read
     */
    private function loadOptionToSample($option)
    {
        $config = $this->pattern->getConfig();
        $options = $config['options'];

        if (isset($options[$option])) {
            return $options[$option];
        } else {
            throw new \InvalidArgumentException($option.' is not an option in the pattern configuration');
        }
    }

    /**
     * Gets value for option for the current step.
     *
     * @param int $step The current step
     * @param int $steps Total number of steps
     * @param array $option Option configuration
     *
     * @return float
     */
    private function getSampleValue($step, $steps, $option)
    {
        $gaps = $steps - 1;
        if ($option['type'] == 'percent') {
            (isset($option['min'])) ? $min = $option['min'] : $min = 0 ;
            (isset($option['max'])) ? $max = $option['max'] : $max = 100 ;
            $delta = $max - $min;
            return ($min + (($delta / $gaps) * ($step - 1))) / 100;
        } else {
            return $option['min'] + ((($option['max'] - $option['min']) / $gaps) * ($step - 1));
        }
    }

    /**
     * Samples the pattern for an option
     *
     * For each option step, this clones the pattern and calls the sample() method
     * with the model as parameter.
     * It then itterates over the parts and calls sampleParts() on them
     *
     * @param \Freesewing\Model $model
     * @param \Freesewing\Themes\* $theme The theme object
     * @param string $optionKey Name of the option
     * @param int $steps Number of steps
     *
     * @return \Freesewing\Patterns\* A pattern object
     */
    public function sampleOptions($model, $theme, $optionKey, $steps = 11)
    {
        $option = $this->loadOptionToSample($optionKey);
        if (!is_int(intval($steps)) or $steps <= 1 or $steps > 25) {
            $steps = 11;
        }
        $renderBot = new \Freesewing\SvgRenderbot();
        for ($i = 1; $i <= $steps; ++$i) {
            /** @var \Freesewing\Patterns\* $p */
            $p = clone $this->pattern;
            $sampleValue = $this->getSampleValue($i, $steps, $option);
            $p->setOption($optionKey, $sampleValue);
            $p->loadParts();
            // clone model so that if the pattern changes it, 
            // we start with a clean slate on the next iteration 
            $m = clone $model;
            $p->sample($m);
            foreach ($p->parts as $partKey => $part) {
                $this->sampleParts($i, $steps, $p, $theme, $renderBot);
            }
        }
        $this->addSampledPartsToPattern();
        $theme->applyRenderMask($this->pattern);
        $this->pattern->layout();

        return $this->pattern;
    }
}
