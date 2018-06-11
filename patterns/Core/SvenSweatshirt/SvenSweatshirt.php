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

    /** Armhole depth factor = 55% */
    const ARMHOLE_DEPTH_FACTOR = 0.55;

    /** Sleevecap height factor = 45% */
    const SLEEVECAP_HEIGHT_FACTOR = 0.45;

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
        $this->setValue('armholeDepth', $model->m('shoulderSlope') / 2 + ( $model->m('bicepsCircumference') + $this->o('bicepsEase') ) * $this->o('armholeDepthFactor'));

        // Heigth of the sleevecap
        $this->setValue('sleevecapHeight', ($model->m('bicepsCircumference') + $this->o('bicepsEase')) * $this->o('sleevecapHeightFactor'));
        
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

        // Hem factor depends on ribbing
        $this->setValue('hemFactor', $this->o('ribbing') ? 1 : 3); 

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
       
        // Do not tweak if the requested pattern is not us but a (grand)child class
        if($this->requested() === __CLASS__) {
            // Tweak the sleeve until it fits in our armhole
            $break = 0;
            do {
                $this->draftSleeve($model);
                if($this->v('sleeveTweakRun')>100) $break = 1;
            } while (abs($this->armholeDelta($model)) > 1 && $break == 0);
            $this->msg('After '.$this->v('sleeveTweakRun').' attemps, the sleeve head is '.round($this->armholeDelta($model),1).'mm off.');
        } else {
            $this->draftSleeve($model);
        }
        
        if($this->o('ribbing')) {
            $this->draftHemRibbing($model);
            $this->draftSleeveRibbing($model);
        }

        // Hide parent blocks
        $this->parts['frontBlock']->setRender(false);
        $this->parts['backBlock']->setRender(false);
        $this->parts['sleeveBlock']->setRender(false);
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
        
        if($this->o('ribbing')) {
            $this->finalizeHemRibbing($model);
            $this->finalizeSleeveRibbing($model);
        } 

        // Is this a paperless pattern?
        if ($this->isPaperless) {
            // Add paperless info to all parts
            $this->paperlessFront($model);
            $this->paperlessBack($model);
            $this->paperlessSleeve($model);
            if($this->o('ribbing')) {
                $this->paperlessHemRibbing($model);
                $this->paperlessSleeveRibbing($model);
            }
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

        if($this->o('ribbing')) {
            // Take ribbing into account for length
            $p->addPoint(6, $p->shift(6,90,$this->o('ribbingHeight')));
            $p->addPoint(4, $p->shift(4,90,$this->o('ribbingHeight')));
        }

        // Make armhole less cut-out
        $shift = [10,18,17]; // Points to shift
        $deltaX = $p->deltaX(10,12)/2; // How far?
        foreach($shift as $id) $p->addPoint($id, $p->shift($id,0,$deltaX));

        // Waist with 15cm ease
        $maxReduce = $p->x(5) - ($model->m('naturalWaist')+150)/4;
        if($maxReduce > 40) $maxReduce = 40;
        $p->newPoint('waist', $p->x(5)-$maxReduce, $p->y(3), 'waist');
        $p->addPoint('waistCpTop', $p->shift('waist', 90, $p->deltaY(5,'waist')/2));
        $p->addPoint('waistCpBottom', $p->shift('waist', -90, ($p->deltaY('waist',6)/3)));
        $this->setValue('waistMaxReduce', $maxReduce);
        
        // Hips with 15cm ease
        $maxReduce = $p->x(6) - ($model->m('hipsCircumference')+150)/4;
        if($maxReduce > 40) $maxReduce = 40;
        $p->newPoint(6, $p->x(6)-$maxReduce, $p->y(6), 'hips');
        $p->addPoint('hemAtHips', $p->shift(6,90,$p->deltaY('waist',6)/3));
        $this->setValue('hipsMaxReduce', $maxReduce);


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

        // Set grid anchor
        $p->clonePoint(4, 'gridAnchor');
        
        // Mark paths for sample service
        $p->paths['seamline']->setSample(true);
    
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

        if($this->o('ribbing')) {
            // Take ribbing into account for length
            $p->addPoint(6, $p->shift(6,90,$this->o('ribbingHeight')));
            $p->addPoint(4, $p->shift(4,90,$this->o('ribbingHeight')));
        }
        
        // Waist with 15cm ease
        $maxReduce = $this->v('waistMaxReduce');
        $p->newPoint('waist', $p->x(5)-$maxReduce, $p->y(3), 'waist');
        $p->addPoint('waistCpTop', $p->shift('waist', 90, $p->deltaY(5,'waist')/2));
        $p->addPoint('waistCpBottom', $p->shift('waist', -90, ($p->deltaY('waist',6)/3)));
        $p->addPoint('hemAtHips', $p->shift(6,90,$this->o('lengthBonus')));
        $this->setValue('waistMaxReduce', $maxReduce);
        
        // Hips with 15cm ease
        $maxReduce = $this->v('hipsMaxReduce');
        $p->newPoint(6, $p->x(6)-$maxReduce, $p->y(6), 'hips');
        $p->addPoint('hemAtHips', $p->shift(6,90,$p->deltaY('waist',6)/3));
        
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
        
        // Set grid anchor
        $p->clonePoint(4, 'gridAnchor');

        // Mark paths for sample service
        $p->paths['seamline']->setSample(true);

        // Store quarter hem length
        $this->setValue('quarterHem', $p->distance(4,6));
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
        // (re-)Drafting sleeveBlock from parent pattern
        $this->draftSleeveBlock($model);
        
        // Cloning points from the sleeveBlock
        $this->clonePoints('sleeveBlock', 'sleeve');

        /** @var Part $p */
        $p = $this->parts['sleeve'];
        
        if($this->o('ribbing')) {
            // Shorten sleeve to account for ribbing
            $p->addPoint(31, $p->shift(31,90,$this->o('ribbingHeight')));
            $p->addPoint(32, $p->shift(32,90,$this->o('ribbingHeight')));
        }
        
        $path = 'M 31 L -5 C -5 20 16 C 21 10 10 C 10 22 17 C 23 28 30 C 29 25 18 C 24 11 11 C 11 27 19 C 26 5 5 L 32';
        $p->newPath('seamline', $path.' z', ['class' => 'fabric']);
        // Store sa base paths
        $p->newPath('saBase', $path);
        $p->newPath('hemBase', 'M 31 L 32');
        $p->paths['saBase']->setRender(false);
        $p->paths['hemBase']->setRender(false);
        
        // Store sleevehead length
        $this->setValue('sleeveheadLength', $p->curveLen(-5,-5,20,16) + $p->curveLen(16,21,10,10) + $p->curveLen(10,10,22,17) + $p->curveLen(17,23,28,30) + $p->curveLen(30,29,25,18) + $p->curveLen(18,14,11,11) + $p->curveLen(11,11,27,19) + $p->curveLen(19,26,5,5));
        
        // Set grid anchor
        $p->clonePoint(3, 'gridAnchor');

        // Mark paths for sample service
        $p->paths['seamline']->setSample(true);

        // Store sleeve hem length
        $this->setValue('sleeveHem', $p->distance(31,32));
        
    }

    /**
     * Calculates the difference between the armhole and sleevehead length
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return float The difference between the armhole and sleevehead
     */
    protected function armholeDelta() 
    {
        $target = $this->v('armholeFrontLength') + $this->v('armholeBackLength') + $this->o('sleevecapEase');
        return ($target - $this->v('sleeveheadLength'));
    }

    /**
     * Drafts the hem ribbing
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftHemRibbing($model)
    {
        /** @var Part $p */
        $p = $this->parts['hemRibbing'];

        $this->draftRibbing($p, $this->v('quarterHem')*4);
    }

    /**
     * Drafts the sleeve ribbing
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftSleeveRibbing($model)
    {
        /** @var Part $p */
        $p = $this->parts['sleeveRibbing'];
        $this->draftRibbing($p, $this->v('sleeveHem'));
    }

    /**
     * Drafts ribbing
     *
     * @param Part $p The part to draft this for
     * @param float $length The length of the seam the ribbing goes on
     *
     * @return void
     */
    protected function draftRibbing($p, $length)
    {
        $lead = 50;
        $space = 25;
        $p->newPoint('topLeft', 0, 0);
        $p->newPoint('topRight', $this->o('ribbingHeight')*2, 0);
        $p->newPoint('topLeftMid', 0, $lead); 
        $p->newPoint('topRightMid', $p->x('topRight'), $lead); 
        $p->newPoint('bottomLeftMid', 0, $lead+$space); 
        $p->newPoint('bottomRightMid', $p->x('topRight'), $lead+$space); 
        $p->newPoint('bottomLeft', 0, $lead+$space+$lead);
        $p->newPoint('bottomRight', $p->x('topRight'), $lead+$space+$lead);
        $p->newPoint('topMid', $this->o('ribbingHeight'), 0);
        $p->newPoint('bottomMid', $this->o('ribbingHeight'),  $lead+$space+$lead);

        $p->newPath('outline', 'M topLeftMid L topLeft L topRight L topRightMid M bottomRightMid L bottomRight L bottomLeft L bottomLeftMid', ['class' => 'various']);
        $p->newPath('help', 'M topLeftMid L bottomLeftMid M topRightMid L bottomRightMid', ['class' => 'various hint']);
        $p->newPath('fold', 'M topMid L bottomMid', ['class' => 'various help']);

        $p->newPath('saBase', 'M topLeft L bottomLeft L bottomRight L topRight z', false, 0);
        $p->paths['saBase']->setRender(false);

        $p->addPoint('dimensionAnchorTop', $p->shift('topRight', 180, 10));
        $p->addPoint('dimensionAnchorBottom', $p->shift('bottomRight', 180, 10));
        $p->newLinearDimension('bottomRight','topRight', -10, $p->unit($length*$this->stretchToScale($this->o('ribbingStretchFactor'))));

        $p->newPoint('titleAnchor', $p->x('topRight')/2, $p->y('bottomRight')/4);

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
            $p->offsetPath('sa','saBase', $this->o('sa')*-1, 1, ['class' => 'fabric sa']);
            $p->offsetPath('hemSa','hemBase', $this->o('sa')*-1*$this->v('hemFactor'), 1, ['class' => 'fabric sa']);
            // Join ends
            $p->newPath('saJoints', 'M sa-endPoint L 9 M sa-startPoint L hemSa-endPoint M hemSa-startPoint L 4', ['class' => 'fabric sa']);
        }
        
        // Title
        $p->newPoint('titleAnchor', $p->x(8), $p->y(5));
        $p->addTitle('titleAnchor', 2, $this->t($p->title), '2x '.$this->t('from fabric')."\n".$this->t('Cut on fold'));

        // Logo
        $p->addPoint('logoAnchor', $p->shift('titleAnchor', -90 ,90));
        $p->newSnippet('logo','logo','logoAnchor');

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
            $p->offsetPath('sa','saBase', $this->o('sa')*-1, 1, ['class' => 'fabric sa']);
            $p->offsetPath('hemSa','hemBase', $this->o('sa')*-1*$this->v('hemFactor'), 1, ['class' => 'fabric sa']);
            // Join ends
            $p->newPath('saJoints', 'M sa-endPoint L 9 M sa-startPoint L hemSa-endPoint M hemSa-startPoint L 4', ['class' => 'fabric sa']);
        }
        
        // Title
        $p->newPoint('titleAnchor', $p->x(8), $p->y(5));
        $p->addTitle('titleAnchor', 1, $this->t($p->title), '2x '.$this->t('from fabric')."\n".$this->t('Cut on fold'));

        // Logo
        $p->addPoint('logoAnchor', $p->shift('titleAnchor', -90 ,90));
        $p->newSnippet('logo','logo','logoAnchor');

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
            $p->offsetPath('sa','saBase', $this->o('sa'), 1, ['class' => 'fabric sa']);
            $p->offsetPath('hemSa','hemBase', $this->o('sa')*-1*$this->v('hemFactor'), 1, ['class' => 'fabric sa']);
            // Join ends
            $p->newPath('saJoints', 'M hemSa-startPoint L sa-startPoint M hemSa-endPoint L sa-endPoint', ['class' => 'fabric sa']);
        }


        // Scalebox
        $p->clonePoint(2,'gridAnchor');
        $p->newSnippet('scalebox', 'scalebox', 'gridAnchor');

        // Title
        $p->addTitle(33, 3, $this->t($p->title), '2x '.$this->t('from fabric')."\n".$this->t('Good sides together'));

        // Logo
        $p->addPoint('logoAnchor', $p->shift(33, 90 ,90));
        $p->newSnippet('logo','logo','logoAnchor');
    }
    
    /**
     * Finalizes the hem ribbing
     *
     * @param \Freesewing\Model $model The model to draft for
     */
    public function finalizeHemRibbing($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['hemRibbing'];

        // Title
        $p->addTitle('titleAnchor', 4, $this->t($p->title), '1x '.$this->t('from ribbing'), ['scale' => 75]);

        // Seam allowance 
        if($this->o('sa')) {
            $p->offsetPath('sa','saBase', $this->o('sa')*-1, 1, ['class' => 'various sa']);
        }
    }

    /**
     * Finalizes the sleeve ribbing
     *
     * @param \Freesewing\Model $model The model to draft for
     */
    public function finalizeSleeveRibbing($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['sleeveRibbing'];
        
        // Title
        $p->addTitle('titleAnchor', 5, $this->t($p->title), '2x '.$this->t('from ribbing'), ['scale' => 75]);
        
        // Seam allowance 
        if($this->o('sa')) {
            $p->offsetPath('sa','saBase', $this->o('sa')*-1, 1, ['class' => 'various sa']);
        }
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
        $p->newWidthDimension(4,6,$p->y(6)+15+(10*$this->v('hemFactor')));

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
        $p->newCurvedDimension('M 5 C 13 16 14 C 15 18 10 C 17 19 12', -25);
        
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
        $p->newWidthDimension(4,6,$p->y(6)+15+(10*$this->v('hemFactor')));

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
        $p->newCurvedDimension('M 5 C 13 16 14 C 15 18 10 C 17 19 12', -25);
        
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
        $p->newWidthDimension(31,32,$p->y(32)+15+(10*$this->v('hemFactor')));

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
        ', 20);
    }
    
    /**
     * Paperless instructions for the hem ribbing
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessHemRibbing($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['hemRibbing'];

        $p->newWidthDimension('bottomLeft', 'bottomRight', $p->y('bottomLeft')+15+$this->o('sa'));

    }

    /**
     * Paperless instructions for the sleeve ribbing
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessSleeveRibbing($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['sleeveRibbing'];
        
        $p->newWidthDimension('bottomLeft', 'bottomRight', $p->y('bottomLeft')+15+$this->o('sa'));
    }

}
