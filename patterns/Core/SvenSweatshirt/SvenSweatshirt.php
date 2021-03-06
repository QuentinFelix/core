<?php
/** Freesewing\Patterns\Core\SvenSweatshirt class */
namespace Freesewing\Patterns\Core;

/**
 * A sweatshirt pattern
 *
 * This is based on the BrianBodyBlock
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2017 Joost De Cock
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, Version 3
 */
class SvenSweatshirt extends BrianBodyBlock
{
    /*
        ___       _ _   _       _ _
       |_ _|_ __ (_) |_(_) __ _| (_)___  ___
        | || '_ \| | __| |/ _` | | / __|/ _ \
        | || | | | | |_| | (_| | | \__ \  __/
       |___|_| |_|_|\__|_|\__,_|_|_|___/\___|

      Things we need to do before we can draft a pattern
    */

    /** Collar ease = 1.5cm */
    const COLLAR_EASE = 95;

    /** Back neck cutout = 2cm */
    const NECK_CUTOUT = 20;

    /** No sleevecap ease, this is for knitwear */
    const SLEEVECAP_EASE = 0;

    /** Armhole depth factor = 70% */
    const ARMHOLE_DEPTH_FACTOR = 0.7;

    /** Sleevecap height factor = 55% */
    const SLEEVECAP_HEIGHT_FACTOR = 0.55;

    /**
     * Sets up options and values for our draft
     *
     * By branching this out of the sample/draft methods, we can
     * set a bunch of options and values the influence the draft
     * without having to touch the sample/draft methods
     * When extending this pattern so we can just implement the
     * initialize() method and re-use the other methods.
     *
     * Good to know:
     * Options are typically provided by the user, but sometimes they are fixed
     * Values are calculated for re-use later
     *
     * @param \Freesewing\Model $model The model to sample for
     *
     * @return void
     */
    public function initialize($model)
    {
        // Needed for Brian
        $this->setOptionIfUnset('collarEase', self::COLLAR_EASE);
        $this->setOptionIfUnset('backNeckCutout', self::NECK_CUTOUT);
        $this->setOptionIfUnset('sleevecapEase', self::SLEEVECAP_EASE);
        $this->setOptionIfUnset('armholeDepthFactor', self::ARMHOLE_DEPTH_FACTOR);
        $this->setOptionIfUnset('sleevecapHeightFactor', self::SLEEVECAP_HEIGHT_FACTOR);
        $this->setValue('shoulderSlope', $model->m('shoulderSlope')); 

        // Depth of the armhole
        $this->setValue('armholeDepth', $model->m('shoulderSlope') / 2 + $model->m('bicepsCircumference') * $this->o('armholeDepthFactor'));

        // Heigth of the sleevecap
        $this->setValue('sleevecapHeight', $model->m('bicepsCircumference') * $this->o('sleevecapHeightFactor'));
        
        // Collar width and depth
        $widerFactor = 1.2;
        $this->setValue('collarWidth', (($model->getMeasurement('neckCircumference') / 2.42) / 2) * $widerFactor);
        $this->setValue('collarDepth', (($model->getMeasurement('neckCircumference') + $this->getOption('collarEase')) / 5 - 8) / $widerFactor);

        // Cut front armhole a bit deeper
        $this->setValue('frontArmholeExtra', 5);
        
        // Tweak factors
        $this->setValue('frontCollarTweakFactor', 1); 
        $this->setValue('frontCollarTweakRun', 0); 
        $this->setValue('sleeveTweakFactor', 1); 
        $this->setValue('sleeveTweakRun', 0); 
    }

    /*
        ____             __ _
       |  _ \ _ __ __ _ / _| |_
       | | | | '__/ _` | |_| __|
       | |_| | | | (_| |  _| |_
       |____/|_|  \__,_|_|  \__|

      The actual sampling/drafting of the pattern
    */

    /**
     * Generates a sample of the pattern
     *
     * Here, you create a sample of the pattern for a given model
     * and set of options. You should get a barebones pattern with only
     * what it takes to illustrate the effect of changes in
     * the sampled option or measurement.
     *
     * @param \Freesewing\Model $model The model to sample for
     *
     * @return void
     */
    public function sample($model)
    {
        // Setup all options and values we need
        $this->initialize($model);

        parent::sample($model);

        $this->draftFront($model);
        $this->draftBack($model);
        
        // Tweak the sleeve until it fits in our armhole
        do {
            $this->draftSleeve($model);
        } while (abs($this->armholeDelta($model)) > 1);
        $this->msg('After '.$this->v('sleeveTweakRun').' attemps, the sleeve head is '.round($this->armholeDelta($model),1).'mm off.');
    }

    /**
     * Generates a draft of the pattern
     *
     * Here, you create the full draft of this pattern for a given model
     * and set of options. You get a complete pattern with
     * all bels and whistles.
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draft($model)
    {
        // Continue from sample
        $this->sample($model);

        parent::draft($model);

        // Hide parent blocks
        $this->parts['frontBlock']->setRender(false);
        $this->parts['backBlock']->setRender(false);
        $this->parts['sleeveBlock']->setRender(false);

        // Finalize parts
        $this->finalizeFront($model);
        $this->finalizeBack($model);
        $this->finalizeSleeve($model);
        
        // Is this a paperless pattern?
        if ($this->isPaperless) {
            // Add paperless info to all parts
            $this->paperlessFront($model);
            $this->paperlessBack($model);
            $this->paperlessSleeve($model);
        }
    }

    /**
     * Drafts the front
     *
     * @param \Freesewing\Model $model The model to draft for
     */
    public function draftFront($model)
    {
        $this->clonePoints('frontBlock','front');

        /** @var \Freesewing\Part $p */
        $p = $this->parts['front'];

        // Make armhole less cut-out
        $shift = [10,18,17]; // Points to shift
        $deltaX = $p->deltaX(10,12)/2; // How far?
        foreach($shift as $id) {
            $p->addPoint($id, $p->shift($id,0,$deltaX));
        }

        // Waist with 15cm ease
        $maxReduce = $p->x(5) - ($model->m('naturalWaist')+150)/4;
        if($maxReduce > 40) $maxReduce = 40;
        $p->newPoint('waist', $p->x(5)-$maxReduce, $p->y(3), 'waist');
        $p->addPoint('waistCpTop', $p->shift('waist', 90, $p->deltaY(5,'waist')/2));
        $p->addPoint('waistCpBottom', $p->shift('waist', -90, ($p->deltaY('waist',6)-$this->o('lengthBonus'))/2));
        $p->addPoint('hemAtHips', $p->shift(6,90,$this->o('lengthBonus')));
        $this->setValue('waistMaxReduce', $maxReduce);

        // Paths
        $path = 'M 9 L 2 L 3 L 4 L 6 C hemAtHips waistCpBottom waist C waistCpTop 5 5 C 13 16 14 C 15 18 10 C 17 19 12 L 8 C 20 21 9 z';
        $p->newPath('seamline', $path, ['class' => 'fabric']);
        // Store sa base paths
        $p->newPath('saBase', 'M 6 C hemAtHips waistCpBottom waist C waistCpTop 5 5 C 13 16 14 C 15 18 10 C 17 19 12 L 8 C 20 21 9'); 
        $p->newPath('hemBase', 'M 4 L 6'); 
        $p->paths['saBase']->setRender(false);
        $p->paths['hemBase']->setRender(false);

        // Store armhole length
        $this->setValue('armholeFrontLength', $p->curveLen(12,19,17,10) + $p->curveLen(10,18,15,14) + $p->curveLen(14,16,13,5));
    }

    /**
     * Drafts the back
     *
     * @param \Freesewing\Model $model The model to draft for
     */
    public function draftBack($model)
    {
        $this->clonePoints('backBlock','back');

        /** @var \Freesewing\Part $p */
        $p = $this->parts['back'];

        // Waist with 15cm ease
        $maxReduce = $this->v('waistMaxReduce');
        $p->newPoint('waist', $p->x(5)-$maxReduce, $p->y(3), 'waist');
        $p->addPoint('waistCpTop', $p->shift('waist', 90, $p->deltaY(5,'waist')/2));
        $p->addPoint('waistCpBottom', $p->shift('waist', -90, ($p->deltaY('waist',6)-$this->o('lengthBonus'))/2));
        $p->addPoint('hemAtHips', $p->shift(6,90,$this->o('lengthBonus')));
        $this->setValue('waistMaxReduce', $maxReduce);
        
        // Paths
        $path = 'M 1 L 2 L 3 L 4 L 6 C hemAtHips waistCpBottom waist C waistCpTop 5 5 C 13 16 14 C 15 18 10 C 17 19 12 L 8 C 20 1 1 z';
        $p->newPath('seamline', $path, ['class' => 'fabric']);
        // Store sa base paths
        $p->newPath('saBase', 'M 6 C hemAtHips waistCpBottom waist C waistCpTop 5 5 C 13 16 14 C 15 18 10 C 17 19 12 L 8 C 20 1 1 '); 
        $p->newPath('hemBase', 'M 4 L 6'); 
        $p->paths['saBase']->setRender(false);
        $p->paths['hemBase']->setRender(false);

        // Store armhole length
        $this->setValue('armholeBackLength', $p->curveLen(12,19,17,10) + $p->curveLen(10,18,15,14) + $p->curveLen(14,16,13,5));
    }
    
    /**
     * Drafts the sleeve
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftSleeve($model)
    {
        // Is this the first time we're calling draftSleeve() ?
        if($this->v('sleeveTweakRun') > 0) {
            // No, this will be a tweaked draft. So let's tweak
            if($this->armholeDelta($model) > 0) {
                //  Armhole is larger than sleeve head. Increase tweak factor 
                $this->setValue('sleeveTweakFactor', $this->v('sleeveTweakFactor')*1.01);
            } else {
                //  Armhole is smaller than sleeve head. Decrease tweak factor 
                $this->setValue('sleeveTweakFactor', $this->v('sleeveTweakFactor')*0.99);
            }
            // Include debug message
            $this->dbg('Sleeve tweak run '.$this->v('sleeveTweakRun').'. Sleeve head is '.$this->armholeDelta($model).'mm off');
        }
        // Keep track of tweak runs because why not
        $this->setValue('sleeveTweakRun', $this->v('sleeveTweakRun')+1);
        
        // (re-)Drafting sleeveBlock from parent pattern
        $this->draftSleeveBlock($model);
        
        // Cloning points from the sleeveBlock
        $this->clonePoints('sleeveBlock', 'sleeve');

        /** @var Part $p */
        $p = $this->parts['sleeve'];
        
        $path = 'M 31 L -5 C -5 20 16 C 21 10 10 C 10 22 17 C 23 28 30 C 29 25 18 C 24 11 11 C 11 27 19 C 26 5 5 L 32';
        $p->newPath('seamline', $path.' z', ['class' => 'fabric']);
        // Store sa base paths
        $p->newPath('saBase', $path);
        $p->newPath('hemBase', 'M 31 L 32');
        $p->paths['saBase']->setRender(false);
        $p->paths['hemBase']->setRender(false);
        
        // Store sleevehead length
        $this->setValue('sleeveheadLength', $p->curveLen(-5,-5,20,16) + $p->curveLen(16,21,10,10) + $p->curveLen(10,10,22,17) + $p->curveLen(17,23,28,30) + $p->curveLen(30,29,25,18) + $p->curveLen(18,14,11,11) + $p->curveLen(11,11,27,19) + $p->curveLen(19,26,5,5));
    }

    /**
     * Calculates the difference between the armhole and sleevehead length
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return float The difference between the armhole and sleevehead
     */
    private function armholeDelta($model) 
    {
        $target = $this->v('armholeFrontLength') + $this->v('armholeBackLength') + $this->o('sleevecapEase');
        return ($target - $this->v('sleeveheadLength'));
    }

    /*
       _____ _             _ _
      |  ___(_)_ __   __ _| (_)_______
      | |_  | | '_ \ / _` | | |_  / _ \
      |  _| | | | | | (_| | | |/ /  __/
      |_|   |_|_| |_|\__,_|_|_/___\___|

      Adding titles/logos/seam-allowance/grainline and so on
    */

    /**
     * Finalizes the back
     *
     * @param \Freesewing\Model $model The model to draft for
     */
    public function finalizeBack($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['back'];
        
        // Seam allowance 
        if($this->o('sa')) {
            $p->offsetPath('sa','saBase', $this->o('sa'), 1, ['class' => 'fabric sa']);
            $p->offsetPath('hemSa','hemBase', $this->o('sa')*3, 1, ['class' => 'fabric sa']);
            // Join ends
            $p->newPath('saJoints', 'M sa-endPoint L 9 M sa-startPoint L hemSa-endPoint M hemSa-startPoint L 4', ['class' => 'fabric sa']);
        }
        
        // Title
        $p->newPoint('titleAnchor', $p->x(8), $p->y(5));
        $p->addTitle('titleAnchor', 2, $this->t($p->title), '2x '.$this->t('from fabric')."\n".$this->t('Cut on fold'));

        // Logo
        $p->addPoint('logoAnchor', $p->shift('titleAnchor', -90 ,90));
        $p->newSnippet('logo','logo-sm','logoAnchor');

        // Cut on fold
        $p->addPoint('cofTop', $p->shift(1,-90,20));
        $p->addPoint('cofBottom', $p->shift(4,90,20));
        $p->newCutonfold('cofBottom','cofTop',$this->t('Cut on fold'));
    }

    /**
     * Finalizes the front
     *
     * @param \Freesewing\Model $model The model to draft for
     */
    public function finalizeFront($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['front'];
        
        // Seam allowance
        if($this->o('sa')) {
            $p->offsetPath('sa','saBase', $this->o('sa'), 1, ['class' => 'fabric sa']);
            $p->offsetPath('hemSa','hemBase', $this->o('sa')*3, 1, ['class' => 'fabric sa']);
            // Join ends
            $p->newPath('saJoints', 'M sa-endPoint L 9 M sa-startPoint L hemSa-endPoint M hemSa-startPoint L 4', ['class' => 'fabric sa']);
        }
        
        // Title
        $p->newPoint('titleAnchor', $p->x(8), $p->y(5));
        $p->addTitle('titleAnchor', 1, $this->t($p->title), '2x '.$this->t('from fabric')."\n".$this->t('Cut on fold'));

        // Logo
        $p->addPoint('logoAnchor', $p->shift('titleAnchor', -90 ,90));
        $p->newSnippet('logo','logo-sm','logoAnchor');

        // Cut on fold
        $p->addPoint('cofTop', $p->shift(9,-90,20));
        $p->addPoint('cofBottom', $p->shift(4,90,20));
        $p->newCutonfold('cofBottom','cofTop',$this->t('Cut on fold'));
    }
    
    /**
     * Finalizes the sleeve
     *
     * @param \Freesewing\Model $model The model to draft for
     */
    public function finalizeSleeve($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['sleeve'];

        // Seam allowance 
        if($this->o('sa')) {
            $p->offsetPath('sa','saBase', $this->o('sa')*-1, 1, ['class' => 'fabric sa']);
            $p->offsetPath('hemSa','hemBase', $this->o('sa')*3, 1, ['class' => 'fabric sa']);
            // Join ends
            $p->newPath('saJoints', 'M hemSa-startPoint L sa-startPoint M hemSa-endPoint L sa-endPoint', ['class' => 'fabric sa']);
        }


        // Scalebox
        $p->newSnippet('scalebox', 'scalebox', 'gridAnchor');

        // Title
        $p->addTitle(33, 3, $this->t($p->title), '2x '.$this->t('from fabric')."\n".$this->t('Good sides together'));

        // Logo
        $p->addPoint('logoAnchor', $p->shift(33, 90 ,90));
        $p->newSnippet('logo','logo','logoAnchor');
    }
    
    /*
        ____                       _
       |  _ \ __ _ _ __   ___ _ __| | ___  ___ ___
       | |_) / _` | '_ \ / _ \ '__| |/ _ \/ __/ __|
       |  __/ (_| | |_) |  __/ |  | |  __/\__ \__ \
       |_|   \__,_| .__/ \___|_|  |_|\___||___/___/
                  |_|

      Instructions for paperless patterns
    */

    /**
     * Paperless instructions for the front 
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessFront($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['front'];

        // Width at the bottom
        $p->newWidthDimension(4,6,$p->y(6)+25);

        // Height at the right
        $xBase = $p->x(5);
        $p->newHeightDimension(6, 5, $xBase+25);
        $p->newHeightDimension(6, 12, $xBase+40);
        $p->newHeightDimension(6, 8, $xBase+55);

        // Height at the left
        $p->newHeightDimension(9, 8, $p->x(9)-15);

        // Width at the top
        $p->newWidthDimension(9,8,$p->y(8)-20);

        // Length of shoulder seam
        $p->newLinearDimension(8,12,-20);

        // Armhole length
        $p->newCurvedDimension('M 5 C 13 16 14 C 15 18 10 C 17 19 12', 25);
        
        // Waist width
        $p->newWidthDimension(3, 'waist');
    }

    /**
     * Paperless instructions for the back 
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessBack($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['back'];

        // Width at the bottom
        $p->newWidthDimension(4,6,$p->y(6)+25);

        // Height at the right
        $xBase = $p->x(5);
        $p->newHeightDimension(6, 5, $xBase+25);
        $p->newHeightDimension(6, 12, $xBase+40);
        $p->newHeightDimension(6, 8, $xBase+55);

        // Height at the left
        $p->newHeightDimensionSm(1, 8, $p->x(9)-15);

        // Width at the top
        $p->newWidthDimension(1,8,$p->y(8)-20);

        // Length of shoulder seam
        $p->newLinearDimension(8,12,-20);

        // Armhole length
        $p->newCurvedDimension('M 5 C 13 16 14 C 15 18 10 C 17 19 12', 25);
        
        // Waist width
        $p->newWidthDimension(3, 'waist');
    }

    /**
     * Paperless instructions for the sleeve
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessSleeve($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['sleeve'];

        // Height on the right
        $xBase = $p->x(5);
        $p->newHeightDimension(32,5,$xBase+20);
        $p->newHeightDimension(32,30,$xBase+35);

        // Width at the bottom
        $p->newWidthDimension(31,32,$p->y(32)+25);

        // Width at the top
        $p->newWidthDimension(-5,5,$p->y(1)-35);

        // Sleevecap length
        $p->newCurvedDimension('
            M -5 
            C -5 20 16
            C 21 10 10
            C 10 22 17
            C 23 28 30
            C 29 25 18
            C 24 11 11
            C 11 27 19
            C 26 5 5
        ', -20);
    }
}
