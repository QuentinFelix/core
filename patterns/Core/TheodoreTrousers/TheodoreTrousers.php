<?php
/** Freesewing\Patterns\Core\TheodoreTrousers class */
namespace Freesewing\Patterns\Core;

use Freesewing\Part;

/**
 * The Theodore Trousers  pattern
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2016 Joost De Cock
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, Version 3
 */
class TheodoreTrousers extends Pattern
{
    /*
        ___       _ _   _       _ _
       |_ _|_ __ (_) |_(_) __ _| (_)___  ___
        | || '_ \| | __| |/ _` | | / __|/ _ \
        | || | | | | |_| | (_| | | \__ \  __/
       |___|_| |_|_|\__|_|\__,_|_|_|___/\___|

      Things we need to do before we can draft a pattern
    */

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
        // Prevent missing sa from causing warnings
        $this->setOptionIfUnset('sa', 0);
        
        // This option is fixed in the legacy code
        $this->setOptionIfUnset('trouserBottomWidth', 226);   
        
        // These allows the TheoTrousers pattern to just extend this pattern with different options
        $this->setValue('legReduction', 0);   
        $this->setValue('frontReduction', 0);   
        $this->setValue('backReduction', 0);   
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
     * Generates a draft of the pattern
     *
     * This creates a draft of this pattern for a given model
     * and set of options. You get a complete pattern with
     * all bels and whistles.
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draft($model)
    {
        $this->sample($model);
        
        // Finalize all parts
        foreach ($this->parts as $key => $part) {
            $this->{'finalize'.ucfirst($key)}($model);
        }
        
        if ($this->isPaperless) {
            // Finalize all parts
            foreach ($this->parts as $key => $part) {
                $this->{'paperless'.ucfirst($key)}($model);
            }
        }
    }

    /**
     * Generates a sample of the pattern
     *
     * This creates a sample of this pattern for a given model
     * and set of options. You get a barebones pattern with only
     * what it takes to illustrate the effect of changes in
     * the sampled option or measurement.
     *
     * @param \Freesewing\Model $model The model to sample for
     *
     * @return void
     */
    public function sample($model)
    {
        $this->initialize($model);

        // Draft all parts
        foreach ($this->parts as $key => $part) {
            $this->{'draft'.ucfirst($key)}($model);
        }
    }
    
    /**
     * Little helper function to figure out how far to rotate
     *
     * @return float angle to rotate
     */
    private function calculateSlashCorner() 
    {
        /** @var Part $p */
        $p = $this->parts['back'];
        $p->addPoint(901, $p->beamsCross(20,19,26,4), 'Slash point');
        $p->newPoint(902, $p->x(901), $p->y(901));
        $step = -0.1;
        $i=0;
        while($p->distance(901,902)<25) {
            $i++;
            $p->addPoint(902, $p->rotate(901, 26, $i*$step));
        }
        return $i*$step;
    }

    /**
     * Drafts the back block
     *
     * I'm using a draft[part name] scheme here but
     * don't let that think that this is something specific
     * to the draft service.
     *
     * This draft method does the basic drafting and is
     * called by both the draft AND sample methods.
     *
     * The difference starts after this method is done.
     * For sample, this is all we need, but draft calls
     * the finalize[part name] method after this.
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftBack($model)
    {
        /** @var Part $p */
        $p = $this->parts['back'];

        $p->newPoint(   0, 0, 0, 'Center front @ Waistline');
        $p->newPoint(   1, 0, 10 + $model->m('seatDepth') - $this->o('waistbandWidth'), 'Center front crutch line');
        $p->newPoint(   2, 0, $p->y(1) + $model->m('inseam') + $this->o('lengthBonus'), 'Center front bottom');
        $p->newPoint( 201, 0 , $p->y(2) + 10);
        $p->newPoint( 202, $this->o('trouserBottomWidth')/4, $p->y(201));
        $p->addPoint( 203, $p->flipX(202));
        $p->newPoint(   3, 0, $p->y(1) + $model->m('inseam')/2 + 50, 'Center front knee');
        $p->newPoint(   4, 0, $p->y(1) - $model->m('seatDepth')/4, 'Center front seat line');
        $p->newPoint(   5, 10 - $model->m('seatCircumference')/8, $p->y(1));
        $p->newPoint(   9, $p->x(5) - $model->m('seatCircumference')/16 - 5 + $this->v('backReduction'), $p->y(5));
        $p->newPoint(  16, $p->x(5) - $p->deltaX(1,5)/4, $p->y(5));
        $p->addPoint(1601, $p->shift(16, 135, 45));
        $p->addPoint(1602, $p->shift(1601, 45, 25));
        $p->addPoint(1603, $p->shift(1601, -135, 45));
        $p->newPoint(  17, $p->x(16), $p->y(4));
        $p->newPoint(  18, $p->x(16), 0);
        $p->newPoint(  19, $p->x(16), $p->y(16) + $p->deltaY(16,18)/2);
        $p->newPoint(  20, $p->x(18) + 20, $p->y(18));
        $p->addPoint(1901, $p->shiftTowards(20, 19,$p->distance(19,20)+25));
        $p->addPoint(  21, $p->shiftTowards(19,20,$p->distance(19,20)+10));
        $p->newPoint(  22, $p->x(9) + $p->deltaX(5,9)/2 - 5 + $this->v('backReduction'), $p->y(9));
        $p->newPoint(  23, $p->x(22), $p->y(22) + 5);
        $p->newPoint(  24, $p->x(20) + $model->m('hipsCircumference')/4 + 45, $p->y(20));
        $p->addPoint(  25, $p->shiftTowards(21,24, $p->distance(21,24)/2));
        $p->addPoint(2501, $p->shiftTowards(25,24, 120));
        $p->addPoint(2502, $p->rotate(2501,25, -90));
        $p->addPoint(2503, $p->shiftTowards(25,24, 12.5));
        $p->addPoint(2504, $p->shiftTowards(25,21, 12.5));
        $p->newPoint(  26, $p->x(17) + $model->m('seatCircumference')/4 + 30, $p->y(17));
        $p->addPoint(2601, $p->shiftTowards(24,26, $p->deltaY(24,26)*1.4));
        $p->newPoint(  27, $p->x(2) + $this->o('trouserBottomWidth')/2 + 20 - $this->v('legReduction')/2, $p->y(2));
        $p->newPoint(2701, $p->x(27), $p->y(27) - 50);
        $p->addPoint(2702, $p->flipX(2701));
        $p->addPoint(  28, $p->flipX(27));
        $p->newPoint(  29, $p->x(3) + $this->o('trouserBottomWidth')/2 + 15 + 20 - $this->v('legReduction')/2, $p->y(3));
        $p->addPoint(2901, $p->shiftTowards(27,29, $p->deltaY(29,27)*1.6));
        $p->addPoint(  30, $p->flipX(29));
        $p->addPoint(3001, $p->shiftTowards(28,30, $p->deltaY(30,28)*1.6));
        
        // Slash and rotate around point 26
        $corner = $this->calculateSlashCorner();
        $rotateThese = [24, 2501, 2502, 2503, 2504, 25, 21, 20, 19, 1901, 2601];
        foreach ($rotateThese as $id) {
            $newId = "90$id";
            $p->addPoint($newId, $p->rotate($id, 26, $corner), "Rotated $id [$newId]");
        }

        // These need to be moved 1cm to the left as per instructions after slash
        $p->addPoint(901601, $p->shift(1601, 180, 10), 'Shifted 1601 [901601]');
        $p->addPoint(901602, $p->shift(1602, 180, 10), 'Shifted 1602 [901602]');
        $p->addPoint(901603, $p->shift(1603, 180, 10), 'Shifted 1603 [901603]');
        // Add endpoint for pleat line
        $p->addPoint(900, $p->beamsCross(9021, 9024, 0, 1), 'Pleat line end point [900]');

        // Points without seam allowance
        $p->addPoint(    -21, $p->shiftTowards(9021, 902504, 10));
        $p->addPoint(  -2101, $p->shift(-21,$p->angle(9020,9021), -10));
        $p->addPoint(    -24, $p->shiftTowards(9024,9021,10));
        $p->addPoint(  -2104, $p->shift(-24,$p->angle(9024,26), 10));
        $p->addPoint(  -2501, $p->shiftTowards(902504, 902502, 10));
        $p->addPoint(  -2502, $p->shiftTowards(902503, 902502, 10));
        $p->addPoint(  -2503, $p->shiftTowards(902504, 902502, 60));
        $p->addPoint(  -2504, $p->shiftTowards(902503, 902502, 60));
        $p->addPoint(    -26, $p->shift(26,$p->angle(9024,9021),10));
        $p->addPoint(  -2601, $p->shift(902601,$p->angle(26,902601)-90,10));
        $p->addPoint(  -2901, $p->shift(2901,$p->angle(2901,29)-90,10));
        $p->addPoint(    -29, $p->shift(29,$p->angle(2901,29)-90,10));
        $p->addPoint(  -2701, $p->shift(2701,180,10));
        $p->addPoint(    -27, $p->shiftAlong(27,27,202,201,10));
        $p->addPoint(    -28, $p->flipX(-27));
        $p->addPoint(  -2702, $p->flipX(-2701));
        $p->addPoint(    -30, $p->flipX(-29));
        $p->addPoint(  -3001, $p->shift(3001,$p->angle(30,3001)+90,-10));
        $p->addPoint(    -23, $p->shiftAlong(23,23,901603,901601,10));
        $p->addPoint(  -2301, $p->shiftAlong(-23,-23,-3001,-30,10));
        $p->newPoint(   -900, $p->x(900), $p->y(900)+10, 'Pleat line end point');
        $p->addPoint(-901603, $p->shift(901603,$p->angle(901603,901601)+90,-10));
        $p->addPoint(-901601, $p->shift(901601,$p->angle(901603,901601)+90,-10));
        $p->addPoint(-901602, $p->shift(901602,$p->angle(901603,901601)+90,-10));
        $p->addPoint(-901901, $p->shift(901901,$p->angle(901901,9019)+90,-10));
        $p->addPoint(  -9019, $p->shift(9019,$p->angle(901901,9019)+90,-10));

        // Extra SA at back seam
        $p->addPoint(9021, $p->shift(-21,$p->angle(-2501,-2101)+0,40));
  
        // Extra SA at hem
        $p->addPoint( -2710, $p->shift(-27,-90,60));
        $p->addPoint( -2810, $p->flipX(-2710));
        $p->addPoint(-20110, $p->shift(201,-90,60));
        $p->addPoint(-20210, $p->shift(202,-90,60));
        $p->addPoint(-20310, $p->shift(203,-90,60));
  
        // Raise waistband 
        $p->addPoint(66601, $p->shiftTowards(-2101, -21, $this->o('backRise')));
        
        // Original dart without raise
        $p->addPoint(66602, $p->beamsCross(66601, -2104, 902502, -2501));
        $p->addPoint(66603, $p->beamsCross(66601, -2104, 902502, -2502));
        $p->addPoint(66605, $p->shiftTowards(-2101, -21, $this->o('backRise')+10));
        $p->newPoint(66606, $p->x(66602)+$p->deltaX(66601,66605), $p->y(66602)+$p->deltaY(66601,66605));
        $p->addPoint(66607, $p->beamsCross(66606, 66605, 901601, 9021));
        
        // Construct the back dart with raise
        $p->addPoint('dartTop', $p->shiftTowards(66601,-2104,$p->distance(66601,-2104)/2));
        $p->addPoint('dartTip', $p->shift('dartTop',$p->angle(-2104,66601)+90,$p->distance('dartTop',902502)));
        $p->addPoint('dartTopLeft', $p->shiftTowards('dartTop',66601,$p->distance(66602,66603)/2));
        $p->addPoint('dartTopRight', $p->shiftTowards('dartTop',-2104,$p->distance(66602,66603)/2));

        // Reconstruct back pocket with raise
        $p->addPoint('pocketCenterLeft', $p->shiftTowards('dartTopLeft','dartTip',60));
        $p->addPoint('pocketCenterRight', $p->shiftTowards('dartTopRight','dartTip',60));
        $p->addPoint('pocketEdgeLeft', $p->shift('pocketCenterLeft',$p->angle('pocketCenterLeft','dartTip')-90,70));
        $p->addPoint('pocketEdgeRight', $p->shift('pocketCenterRight',$p->angle('pocketCenterRight','dartTip')+90,70));
  
        // Extend back pleat to include rise
        $p->addPoint(-900, $p->beamsCross(900,-900,66601,66602)); 
  
        // Paths
        // This is the original Aldrich path, which includes seam allowance
        //$aldrich = 'M 23 C 23 901603 901601 C 901602 901901 9019 L 9021 L 902504 L 902502 L 902503 L 9024 L 26 C 902601 2901 29 C 29 2701 27 C 27 202 201 C 203 28 28 C 2702 30 30 C 3001 23 23 z';
        //$p->newPath('aldrich',$aldrich, ['class' => 'debug']);

        // This is the path we use, no seam allowance
        if($this->o('backRise') > 0) $noHem = 'C -2702 -30 -30 C -3001 -2301 -2301 C -2301 -901603 -901601 C -901602 -901901 -9019 L 66601 L dartTopLeft L dartTip L dartTopRight L -2104 L -26 C -2601 -2901 -29 C -29 -2701 -27 ';
        else  $noHem = 'C -2702 -30 -30 C -3001 -2301 -2301 C -2301 -901603 -901601 C -901602 -901901 -9019 L -2101 L -2501 L 902502 L -2502 L -2104 L -26 C -2601 -2901 -29 C -29 -2701 -27';
        $seamline = 'M -27 C -27 202 201 C 203 -28 -28 '.$noHem.' z';

        $p->newPath('seamline',$seamline, ['class' => 'fabric']);
        
        // Store base path for SA, but strip out dart
        $p->newPath('saBase','M -28 '.str_replace('L 902502','',str_replace('L dartTip','',$noHem)));
        $p->paths['saBase']->setRender(false);
        
        // Store base path for hem SA
        $p->newPath('hemBase', 'M -27 C -27 202 201 C 203 -28 -28');
        $p->paths['hemBase']->setRender(false);

        // Mark path for sample service
        $p->paths['seamline']->setSample(true);
    
        // Store length of the inseam and side seam
        $this->setValue('backInseamLength', $p->curveLen(-2301,-3001,-30,-30) + $p->curveLen(30,30,-2702,-28));
        $this->setValue('backSideseamLength', $p->distance(-2104,-26)+$p->curveLen(-26,-2601,-2901,-29) + $p->curveLen(-29,-29,-2701,-27));
    }


    /**
     * Drafts the front block
     *
     * I'm using a draft[part name] scheme here but
     * don't let that think that this is something specific
     * to the draft service.
     *
     * This draft method does the basic drafting and is
     * called by both the draft AND sample methods.
     *
     * The difference starts after this method is done.
     * For sample, this is all we need, but draft calls
     * the finalize[part name] method after this.
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftFront($model)
    {
        /** @var Part $p */
        $p = $this->parts['front'];

        $p->newPoint(     0 , 0, 0, 'Center front waistline');
        $p->newPoint(     1 , $p->x(0), $model->m('seatDepth') - $this->o('waistbandWidth') + 10, 'Center front crutch line');
        $p->newPoint(     2 , $p->x(0) , $p->y(1) + $model->m('inseam') + $this->o('lengthBonus'), 'Center front bottom');
        $p->newPoint(     3 , $p->x(0) , $p->y(1) + $model->m('inseam')/2 + 50, 'Center front knee');
        $p->newPoint(     4 , $p->x(0) , $p->y(1) - $model->m('seatDepth')/4, 'Center front seat line');
        $p->newPoint(     5 , $p->x(0) - $model->m('seatCircumference')/8 + 10 , $p->y(1));
        $p->addPoint(   501 , $p->shift(5, 135, 30));
        $p->addPoint(   502 , $p->shift(501, 45, 30));
        $p->addPoint(   503 , $p->shift(501, -135, 30));
        $p->newPoint(     6 , $p->x(5) , $p->y(4));
        $p->newPoint(     7 , $p->x(5) , $p->y(0));
        $p->newPoint(     8 , $p->x(6) + $model->m('seatCircumference')/4 + 20 , $p->y(6));
        $p->newPoint(   801 , $p->x(8) , $p->y(8) - $model->m('seatDepth')/4);
        $p->newPoint(   802 , $p->x(8) , $p->y(8) + $model->m('seatDepth')/4);
        $p->newPoint(     9 , $p->x(5) - $model->m('seatCircumference')/16 - 5 + $this->v('frontReduction'), $p->y(5));
        $p->newPoint(    10 , $p->x(7) + 10 , $p->y(7));
        $p->addPoint(  1001 , $p->shiftTowards(10,6,10));
        $p->newPoint(  1002 , $p->x(0), $p->y(1001));
        $p->newPoint(    11 , $p->x(10) + $model->m('hipsCircumference')/4 + 25 , $p->y(10));
        $p->newPoint(    12 , $p->x(2) + $this->o('trouserBottomWidth')/2 - $this->v('legReduction')/2, $p->y(2));
        $p->newPoint(  1201 , $p->x(12) , $p->y(12) - 50);
        $p->addPoint(    13 , $p->flipX(12));
        $p->addPoint(  1301 , $p->flipX(1201));
        $p->newPoint(    14 , $p->x(3) + $this->o('trouserBottomWidth')/2 + 15 - $this->v('legReduction')/2, $p->y(3));
        $p->addPoint(  1401 , $p->shiftTowards(12,14, $p->distance(12,14)+$p->deltaY(1,3)/2));
        $p->addPoint(  1402 , $p->flipX(1401));
        $p->addPoint(    15 , $p->flipX(14));
        $p->addPoint(    40 , $p->shiftAlong(1001, 1002, 11, 11, 50), 'Fly top');
        $p->newPoint(    41 , $p->x(40) + $p->deltaX(1001,6), $p->y(40) + $p->deltaY(1001,6), 'Fly 6');
        $p->addPoint(    42 , $p->shiftAlong(6, 6, 502, 501, $p->curveLen(6, 6, 502, 501)/2), 'Fly bottom');
        $p->addPoint(    43 , $p->shift(42, -35, 10), 'Fly pretip'); 
        $p->addPoint(    '43beam' , $p->shift(42, -35, 40), 'Fly pretip'); // We use this to find curve intersection later
        $p->addPoint(    44 , $p->shift(43, 0, 20), 'Fly cp1');
        $p->addPoint(    45 , $p->shiftTowards(40, 41, $p->distance(40,41)+20), 'Fly cp2');
        $p->addPoint( -1001 , $p->shiftAlong(1001,1002,11,11,10));
        $p->addPoint(-100101, $p->shift(-1001,$p->angle(1001,6),10));
        $p->addPoint(  -1002, $p->shift(1002,90,-10));
        $p->addPoint(    -11, $p->shiftAlong(11,11,801,8,10));
        $p->addPoint(  -1101, $p->shiftAlong(11,11,1002,1001,10));
        $p->newPoint(  -1102, $p->x(-1101)+$p->deltaX(11,-11), $p->y(-1101)+$p->deltaY(11,-11));
        $p->addPoint(   -801, $p->shift(801,0,-10));
        $p->addPoint(     -8, $p->shift(8,0,-10));
        $p->addPoint(   -802, $p->shift(802,0,-10));
        $p->addPoint(  -1401, $p->shift(1401,$p->angle(1401,14)+90,-10));
        $p->addPoint(    -14, $p->shift(14,$p->angle(1401,14)+90,-10));
        $p->addPoint(  -1201, $p->shift(1201,0,-10));
        $p->addPoint(    -12, $p->shift(12,0,-10));
        $p->addPoint(    -13, $p->shift(13,0,10));
        $p->addPoint(  -1301, $p->shift(1301,0,10));
        $p->addPoint(    -15, $p->flipX(-14));
        $p->addPoint(  -1402, $p->flipX(-1401));
        $p->addPoint(   -901, $p->shiftAlong(9,9,503,501,10));
        $p->addPoint(   -902, $p->shiftAlong(9,9,1402,15,10));
        $p->newPoint(     -9, $p->x(-902)+$p->deltaX(9,-901),$p->y(-902)+$p->deltaY(9,-901));
        $p->addPoint(    -501, $p->shift(501,$p->angle(503,502)-90,10));
        $p->addPoint(    -502, $p->shift(502,$p->angle(503,502)-90,10));
        $p->addPoint(    -503, $p->shift(503,$p->angle(503,502)-90,10));
        $p->addPoint(      -6, $p->shift(6,  $p->angle(6,1001)-90,10));
        $p->addPoint(     -40, $p->shiftTowards(40,41,10));

        // Smooth fly curve a bit at -6
        $p->addPoint('-6cp', $p->shiftTowards(-100101,-6,$p->distance(-100101,-6)+$p->distance(-6,-502)/2));
        
        // Make sure fly ends on curve
        $p->curveCrossesLine(-501,-502,'-6cp',-6,42,'43beam','flyPretipX');
        $p->newPoint(43, $p->x('flyPretipX1'), $p->y('flyPretipX1'));

        // Slant pocket
        $p->addPoint(   60, $p->shiftAlong(-1102, -1102, -1002, -100101,50));
        $curvelen = $p->curveLen(-1102, -1102, -801, -8);
        if($curvelen>=190) $p->addPoint(   61, $p->shiftAlong(-1102, -1102, -801, -8,190));
        else $p->addPoint(   61, $p->shiftAlong(-8,-802,-1401,-14,190-$curvelen));

        // Paths      
        // This is the original Aldrich path, which includes seam allowance
        //$aldrich = 'M 9 C 9 503 501 C 502 6 6 L 1001 C 1002 11 11 C 11 801 8 C 802 1401 14 C 14 1201 12 L 13 C 1301 15 15 C 1402 9 9 z';
        //$p->newPath('aldrich', $aldrich, ['class' => 'debug']);
        
        // This is the path we use, no seam allowance
        $noHem = ' C -1301 -15 -15 C -1402 -9 -9 C -9 -503 -501 C -502 -6cp -6 L -100101 C -1002 -1102 -1102 C -1102 -801 -8 C -802 -1401 -14 C -14 -1201 -12 ';
        $p->newPath('seamline', 'M -12 L -13'. $noHem.' z', ['class' => 'fabric']);

        // Store path as base for seam allowance
        $p->newPath('saBase', 'M -13 '.$noHem);
        $p->paths['saBase']->setRender(false);
        $p->newPath('hemBase', 'M -12 L -13');
        $p->paths['hemBase']->setRender(false);

        // Mark path for sample service
        $p->paths['seamline']->setSample(true);

        // Store length of the inseam and side seam
        $this->setValue('frontInseamLength', $p->curveLen(-9,-9,-1402,-15) + $p->curveLen(-15,-15,-1301,-13));
        $this->setValue('frontSideseamLength', $p->curveLen(-1102,-1102,-801,-8) + $p->curveLen(-8,-802,-1401,-14) + $p->curveLen(-14,-14,-1201,-12));

        // FIXME: Adjust seam length
        $this->msg('Inseam length back is '.$this->v('backInseamLength').' while front is '.$this->v('frontInseamLength'));
        $this->msg('Sideseam length back is '.$this->v('backSideseamLength').' while front is '.$this->v('frontSideseamLength'));
    }

    /**
     * Drafts the waistband interfacing left part
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftWaistbandInterfacingLeft($model)
    {
        /** @var Part $p */
        $p = $this->parts['waistbandInterfacingLeft'];
        
        $p->newPoint(0 , 0, 0, 'Top left');
        $p->newPoint(2 , $this->o('waistbandWidth'), $model->m('hipsCircumference')/2 + 60, 'Bottom right');
        $p->newPoint(1 , $p->x(2),$p->y(0), 'Top right');
        $p->newPoint(3 , $p->x(0),$p->y(2), 'Bottom left');
        
        $p->newPath('outline', 'M 0 L 1 L 2 L 3 z', ['class' => 'interfacing']);
    }

    /**
     * Drafts the waistband interfacing right part
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftWaistbandInterfacingRight($model)
    {
        /** @var Part $p */
        $p = $this->parts['waistbandInterfacingRight'];
        
        $p->newPoint(0 , 0, 0, 'Top left');
        $p->newPoint(2 , $this->o('waistbandWidth'), $model->m('hipsCircumference')/2 + 40, 'Bottom right');
        $p->newPoint(1 , $p->x(2),$p->y(0), 'Top right');
        $p->newPoint(3 , $p->x(0),$p->y(2), 'Bottom left');
        
        $p->newPath('outline', 'M 0 L 1 L 2 L 3 z', ['class' => 'interfacing']);
    }

    /**
     * Drafts the waistband left part
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftWaistbandLeft($model)
    {
        // Cloning points
        $this->clonePoints('waistbandInterfacingLeft', 'waistbandLeft');

        /** @var Part $p */
        $p = $this->parts['waistbandLeft'];
        
        $p->newPath('outline', 'M 0 L 1 L 2 L 3 z', ['class' => 'fabric']);
    }

    /**
     * Drafts the waistband right part
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftWaistbandRight($model)
    {
        // Cloning points
        $this->clonePoints('waistbandInterfacingRight', 'waistbandRight');

        /** @var Part $p */
        $p = $this->parts['waistbandRight'];
        
        $p->newPath('outline', 'M 0 L 1 L 2 L 3 z', ['class' => 'fabric']);
    }

    /**
     * Drafts the waistband lining left part
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftWaistbandLiningLeft($model)
    {
        // Cloning points
        $this->clonePoints('waistbandInterfacingLeft', 'waistbandLiningLeft');

        /** @var Part $p */
        $p = $this->parts['waistbandLiningLeft'];

        // Make 8cm wider
        $p->addPoint(0, $p->shift(0,180,80));
        $p->addPoint(3, $p->shift(3,180,80));
        
        $p->newPath('outline', 'M 0 L 1 L 2 L 3 z', ['class' => 'lining']);
    }

    /**
     * Drafts the waistband lining right part
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftWaistbandLiningRight($model)
    {
        // Cloning points
        $this->clonePoints('waistbandInterfacingRight', 'waistbandLiningRight');

        /** @var Part $p */
        $p = $this->parts['waistbandLiningRight'];
        
        // Make 8cm wider
        $p->addPoint(0, $p->shift(0,180,80));
        $p->addPoint(3, $p->shift(3,180,80));
        
        $p->newPath('outline', 'M 0 L 1 L 2 L 3 z', ['class' => 'lining']);
    }

    /**
     * Drafts the fly piece
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftFlyPiece($model)
    {
        // Cloning points
        $this->clonePoints('front', 'flyPiece');

        /** @var Part $p */
        $p = $this->parts['flyPiece'];

        // We need to split the crotch curve at the bottom of the fly
        $p->splitCurve(-501,-502,'-6cp',-6,43,'fly');
        
        // We need to split the waist curve at the edge of the fly
        $p->splitCurve(-100101,-1002,-1102,-1102,-40,'waistFly');

        // Path 
        $p->newPath('outline', 'M -100101 L -6 C fly6 fly7 43 C 44 45 41 L -40 C waistFly3 waistFly2 -100101 z', ['class' => 'fabric']);
    }

    /**
     * Drafts the fly shield
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftFlyShield($model)
    {
        // Cloning points
        $this->clonePoints('flyPiece', 'flyShield');

        /** @var Part $p */
        $p = $this->parts['flyShield'];

        // Add points to make shift not impact curves
        $p->addPoint('leftTop', $p->shift(-100101,180,20));
        $p->addPoint('old43', $p->shift(43,180,0));

        // Shift the rest
        $shiftThese = [-6, 'fly6', 'fly7', 43];
        foreach($shiftThese as $shiftThis) $p->addPoint($shiftThis, $p->shift($shiftThis,180,20));
        

        // Path 
        $p->newPath('outline', 'M leftTop L -6 C fly6 fly7 43 L old43 C 44 45 41 L -40 C waistFly3 waistFly2 -100101 L leftTop z', ['class' => 'lining']);
    }

    /**
     * Drafts the side piece
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftSidePiece($model)
    {
        // Cloning points
        $this->clonePoints('front', 'sidePiece');

        /** @var Part $p */
        $p = $this->parts['sidePiece'];

        // Add points
        $p->addPoint('topLeft', $p->shiftAlong(-1102,-1102,-1002,-100101,100));
        $p->addPoint('bottomLeft', $p->shift(61,180,50));

        // Split waist curve
        $p->splitCurve(-1102,-1102,-1002,-100101,'topLeft','waist');
        
        // Split side curve
        $p->splitCurve(-8,-802,-1401,-14,61,'side');
        
        // Paths 
        $p->newPath('outline', 'M topLeft L bottomLeft L 61 C side3 side2 -8 C -801 -1102 -1102 C -1102 waist3 topLeft z', ['class' => 'fabric']);
        $p->newPath('pocket', 'M 60 L 61', ['class' => 'help fabric']);
    }

    /**
     * Drafts the front pocket bag
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftFrontPocketBag($model)
    {
        // Cloning points
        $this->clonePoints('front', 'frontPocketBag');

        /** @var Part $p */
        $p = $this->parts['frontPocketBag'];

        // Additional points
        $p->newPoint(810 , $p->x(60),$p->y(-1102)+300);
        $p->newPoint(811 , $p->x(-8),$p->y(810));
        $p->newPoint(812 , $p->x(0),$p->y(810));
        $p->addPoint(813 , $p->shiftTowards(-100101,-6, 70));
        $p->addPoint(814 , $p->shift(813,0,40));
        $p->newPoint(815 , $p->x(0), $p->y(814));
        $p->newPoint(816 , $p->x(-40)-$this->o('sa'), $p->y(-40)-$this->o('sa'));
        $p->addPoint(817 , $p->shift(813,0,30));
        $p->newPoint(818 , $p->x(814)-$this->o('sa'),$p->y(814)+$this->o('sa'));

        // Paths
        $p->newPath('outline', 'M -100101 C -1002 -1102 -1102 C -1102 -801 -8 C -802 811 810 C 812 815 814 L 813 z', ['class' => 'lining']); 
        $p->newPath('flyEdge', 'M -40 L 814', ['class' => 'lining']);
        if($this->o('sa')) $p->newPath('flyEdgeSa', 'M 816 L 818', ['class' => 'sa lining']);
        $p->newPath('pocket', 'M 60 L 61', ['class' => 'help lining']);
    }

    /**
     * Drafts the back inner pocket bag
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftBackInnerPocketBag($model)
    {
        /** @var Part $p */
        $p = $this->parts['backInnerPocketBag'];
        
        $p->newPoint(  0 , -80, 0, 'Top left');
        $p->newPoint(  2 , 80, 230, 'Bottom right');
        $p->newPoint(  1 , $p->x(2),$p->y(0), 'Top right');
        $p->newPoint(  3 , $p->x(0),$p->y(2), 'Bottom left');
        
        $p->addPoint( 31 , $p->shift(3,0,25));
        $p->addPoint( 32 , $p->shift(31,180,\Freesewing\BezierToolbox::bezierCircle(25)));
        $p->addPoint( 33 , $p->shift(3,90,25));
        $p->addPoint( 34 , $p->shift(33,-90,\Freesewing\BezierToolbox::bezierCircle(25)));
        $p->addPoint( 21 , $p->flipX(31));
        $p->addPoint( 22 , $p->flipX(32));
        $p->addPoint( 23 , $p->flipX(33));
        $p->addPoint( 24 , $p->flipX(34));

        $p->newPoint(  5 , -65, 55);
        $p->addPoint(  6 , $p->flipX (5));
        $p->addPoint(  7 , $p->shift(5,90,5));
        $p->addPoint(  8 , $p->shift(5,-90,5));
        $p->addPoint(  9 , $p->flipX(7));
        $p->addPoint( 10 , $p->flipX(8));

        // Paths
        $p->newPath('outline', 'M 0 L 33 C 34 32 31 L 21 C 22 24 23 L 1 z', ['class' => 'lining']);
        $p->newPath('welt', 'M 7 L 8 M 9 L 10 M 5 L 6', ['class' => 'help lining']);
    }

    /**
     * Drafts the back outer pocket bag
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftBackOuterPocketBag($model)
    {
        // Cloning points
        $this->clonePoints('backInnerPocketBag', 'backOuterPocketBag');

        /** @var Part $p */
        $p = $this->parts['backOuterPocketBag'];

        // Make bag 2cm longer
        $shiftThese = [21,22,23,24,31,32,33,34];
        foreach($shiftThese as $shiftThis) $p->addPoint($shiftThis, $p->shift($shiftThis,-90,20));
        
        // Shift welt 1cm down
        $shiftThese = [5,6,7,8,9,10];
        foreach($shiftThese as $shiftThis) $p->addPoint($shiftThis, $p->shift($shiftThis,-90,10));
        
        // Paths
        $p->newPath('outline', 'M 0 L 33 C 34 32 31 L 21 C 22 24 23 L 1 z', ['class' => 'lining']);
        $p->newPath('welt', 'M 7 L 8 M 9 L 10 M 5 L 6', ['class' => 'help lining']);
    }

    /**
     * Drafts the back pocket facing
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftBackPocketFacing($model)
    {
        // Cloning points
        $this->clonePoints('backInnerPocketBag', 'backPocketFacing');

        /** @var Part $p */
        $p = $this->parts['backPocketFacing'];

        // Shift welt 1cm up
        $shiftThese = [5,6,7,8,9,10];
        foreach($shiftThese as $shiftThis) $p->addPoint($shiftThis, $p->shift($shiftThis,-90,-10));
        
        // Bottom corners
        $p->newPoint(2, $p->x(2),90);
        $p->newPoint(3, $p->x(3),90);

        // Paths
        $p->newPath('outline', 'M 0 L 3 L 2 L 1 z', ['class' => 'fabric']);
        $p->newPath('welt', 'M 7 L 8 M 9 L 10 M 5 L 6', ['class' => 'hint']);
    }

    /**
     * Drafts the back pocket inter facing
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftBackPocketInterfacing($model)
    {
        // Cloning points
        $this->clonePoints('backInnerPocketBag', 'backPocketInterfacing');

        /** @var Part $p */
        $p = $this->parts['backPocketInterfacing'];

        // Shift welt 2cm up
        $shiftThese = [5,6,7,8,9,10];
        foreach($shiftThese as $shiftThis) $p->addPoint($shiftThis, $p->shift($shiftThis,-90,-20));
        
        // Bottom corners
        $p->newPoint(2, $p->x(2),70);
        $p->newPoint(3, $p->x(3),70);

        // Paths
        $p->newPath('outline', 'M 0 L 3 L 2 L 1 z', ['class' => 'interfacing']);
        $p->newPath('welt', 'M 7 L 8 M 9 L 10 M 5 L 6', ['class' => 'hint']);
    }

    /**
     * Drafts the belt loop
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftBeltLoop($model)
    {
        /** @var Part $p */
        $p = $this->parts['beltLoop'];

        $p->newPoint(     0 , 0, 0, 'Top left');
        $p->newPoint(     2 , 22, $this->o('waistbandWidth') + 30, 'Bottom right');
        $p->newPoint(     1 , $p->x(2),$p->y(0), 'Top right');
        $p->newPoint(     3 , $p->x(0),$p->y(2), 'Bottom left');
        
        // Paths
        $p->newPath('outline', 'M 0 L 3 L 2 L 1 z', ['class' => 'fabric']);
    }

    /*
       _____ _             _ _
      |  ___(_)_ __   __ _| (_)_______
      | |_  | | '_ \ / _` | | |_  / _ \
      |  _| | | | | | (_| | | |/ /  __/
      |_|   |_|_| |_|\__,_|_|_/___\___|

      Adding titles/logos/seam-allowance/measurements and so on
    */

    /**
     * Finalizes the back block
     *
     * Only draft() calls this method, sample() does not.
     * It does things like adding a title, logo, and any
     * text or instructions that go on the pattern.
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeBack($model)
    {
        /** @var Part $p */
        $p = $this->parts['back'];
        
        // Seam and hem allowance
        if($this->o('sa')) {
            $p->offsetPath('sa','saBase', $this->o('sa'), 1, ['class' => 'fabric sa']);
            $p->offsetPath('hem','hemBase', $this->o('sa')*6, 1, ['class' => 'fabric sa']);
            // Join SA ends
            $p->newPath('saJoin', 'M sa-startPoint L hem-endPoint M hem-startPoint L sa-endPoint', ['class' => 'fabric sa']);
        }

        // Title
        $p->newPoint('titleAnchor', $p->x(5) + 50, $p->y(5) + 50);
        $p->addTitle('titleAnchor', 1, $this->t($p->title), '2x '.$this->t('from fabric')."\n".$this->t('Good sides together'));

        // Logo
        $p->addPoint('logoAnchor', $p->shift('titleAnchor',-90,90));
        $p->newSnippet('logo', 'logo', 'logoAnchor');
        
        // Scalebox
        $p->addPoint('scaleboxAnchor', $p->shift('logoAnchor',-90,40));
        $p->newSnippet('scalebox', 'scalebox', 'scaleboxAnchor');

        // Grainline 
        $p->newPoint('grainlineTop',$p->x(900), $p->y(900));
        $p->newPoint('grainlineBottom', $p->x(201), $p->y(201));
        $p->newGrainline('grainlineBottom', 'grainlineTop',$this->t('Grainline').' + '.$this->t('Pleat'));

        // Pocket
        $pocket = 'M pocketEdgeLeft L pocketCenterLeft M pocketCenterRight L pocketEdgeRight';
        $p->newPath('pocket',$pocket,['class' => 'help fabric']);

        // Notches
        $p->notch(['pocketEdgeLeft','pocketEdgeRight']);

    }

    /**
     * Finalizes the front block
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeFront($model)
    {
        /** @var Part $p */
        $p = $this->parts['front'];

        // Seam and hem allowance
        if($this->o('sa')) {
            $p->offsetPath('sa','saBase', $this->o('sa'), 1, ['class' => 'fabric sa']);
            $p->offsetPath('hem','hemBase', $this->o('sa')*6, 1, ['class' => 'fabric sa']);
            // Join SA ends
            $p->newPath('saJoin', 'M sa-startPoint L hem-endPoint M hem-startPoint L sa-endPoint', ['class' => 'fabric sa']);
        }

        // Title
        $p->newPoint('titleAnchor' , $p->x(5) + 50 , $p->y(5) + 50, 'Title anchor point');
        $p->addTitle('titleAnchor', 2, $this->t($p->title), '2x '.$this->t('from fabric')."\n".$this->t('Good sides together'));

        // Logo
        $p->addPoint('logoAnchor', $p->shift('titleAnchor',-90,90));
        $p->newSnippet('logo', 'logo-sm', 'logoAnchor');

        // Grainline
        $p->newPoint('grainlineTop' , $p->x(2), $p->y(-100101) , 'Grainline anchor point');
        $p->newPoint('grainlineBottom' , $p->x(2), $p->y(2), 'Grainline anchor point');
        $p->newGrainline('grainlineBottom', 'grainlineTop',$this->t('Grainline').' + '.$this->t('Pleat'));

        // Fly
        $p->curveCrossesLine(-100101,-1002,-1102,-1102,41,40,'fly'); // Adds point 'fly1'
        $p->newPath('fly', 'M fly1 L 41 C 45 44 43 L 42', ['class' => 'help fabric']);

        // Pocket
        $p->newPath('pocket', 'M 60 L 61', ['class' => 'help fabric']);
        
        // Lining
        $p->newPath('lining', 'M -14 L -15', ['class' => 'help lining']);

        // Notches
        $p->notch([60,61,43]);
    }

    /**
     * Finalizes the waistband interfacing left part
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeWaistbandInterfacingLeft($model)
    {
        /** @var Part $p */
        $p = $this->parts['waistbandInterfacingLeft'];

        // Title
        $p->newPoint('titleAnchor', $p->x(2)/2, 135);
        $p->addTitle('titleAnchor', '3a', $this->t($p->title), '1x '.$this->t('from interfacing'), ['scale' => 50, 'align' => 'left', 'rotate' => -90]);
    }

    /**
     * Finalizes the waistband interfacing right part
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeWaistbandInterfacingRight($model)
    {
        /** @var Part $p */
        $p = $this->parts['waistbandInterfacingRight'];

        // Title
        $p->newPoint('titleAnchor', $p->x(2)/2, 135);
        $p->addTitle('titleAnchor', '3b', $this->t($p->title), '1x '.$this->t('from interfacing'), ['scale' => 50, 'align' => 'left', 'rotate' => -90]);
    }

    /**
     * Finalizes the waistband left part
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeWaistbandLeft($model)
    {
        /** @var Part $p */
        $p = $this->parts['waistbandLeft'];

        // Title
        $p->newPoint('titleAnchor', $p->x(2)/2, 135);
        $p->addTitle('titleAnchor', '4a', $this->t($p->title), '1x '.$this->t('from fabric'), ['scale' => 50, 'align' => 'left', 'rotate' => -90]);

        // Grainline 
        $p->addPoint('grainlineLeft', $p->shift(3,90,50));
        $p->addPoint('grainlineRight', $p->shift(2,90,50));
        $p->newGrainline('grainlineLeft', 'grainlineRight', $this->t('Grainline'));

        // Seam allowance
        if($this->o('sa')) $p->offsetPath('sa', 'outline', $this->o('sa'), true, ['class' => 'fabric sa']);
    }

    /**
     * Finalizes the waistband right part
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeWaistbandRight($model)
    {
        /** @var Part $p */
        $p = $this->parts['waistbandRight'];

        // Title
        $p->newPoint('titleAnchor', $p->x(2)/2, 135);
        $p->addTitle('titleAnchor', '4b', $this->t($p->title), '1x '.$this->t('from fabric'), ['scale' => 50, 'align' => 'left', 'rotate' => -90]);

        // Grainline 
        $p->addPoint('grainlineLeft', $p->shift(3,90,50));
        $p->addPoint('grainlineRight', $p->shift(2,90,50));
        $p->newGrainline('grainlineLeft', 'grainlineRight', $this->t('Grainline'));

        // Seam allowance
        if($this->o('sa')) $p->offsetPath('sa', 'outline', $this->o('sa'), true, ['class' => 'fabric sa']);
    }


    /**
     * Finalizes the waistband lining left part
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeWaistbandLiningLeft($model)
    {
        /** @var Part $p */
        $p = $this->parts['waistbandLiningLeft'];

        // Title
        $p->newPoint('titleAnchor', $p->x(2)/2-40, 135);
        $p->addTitle('titleAnchor', '5a', $this->t($p->title), '1x '.$this->t('from lining'), ['align' => 'left', 'rotate' => -90]);

        // Grainline 
        $p->addPoint('grainlineLeft', $p->shift(3,90,50));
        $p->addPoint('grainlineRight', $p->shift(2,90,50));
        $p->newGrainline('grainlineLeft', 'grainlineRight', $this->t('Grainline'));

        // Seam allowance
        if($this->o('sa')) $p->offsetPath('sa', 'outline', $this->o('sa'), true, ['class' => 'lining sa']);
    }

    /**
     * Finalizes the waistband lining right part
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeWaistbandLiningRight($model)
    {
        /** @var Part $p */
        $p = $this->parts['waistbandLiningRight'];

        // Title
        $p->newPoint('titleAnchor', $p->x(2)/2-40, 135);
        $p->addTitle('titleAnchor', '5b', $this->t($p->title), '1x '.$this->t('from lining'), ['align' => 'left', 'rotate' => -90]);

        // Grainline 
        $p->addPoint('grainlineLeft', $p->shift(3,90,50));
        $p->addPoint('grainlineRight', $p->shift(2,90,50));
        $p->newGrainline('grainlineLeft', 'grainlineRight', $this->t('Grainline'));

        // Seam allowance
        if($this->o('sa')) $p->offsetPath('sa', 'outline', $this->o('sa'), true, ['class' => 'lining sa']);
    }

    /**
     * Finalizes the fly piece
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeFlyPiece($model)
    {
        /** @var Part $p */
        $p = $this->parts['flyPiece'];

        // Title
        $p->newPoint('titleAnchor', $p->x(-100101)+20, $p->y(-100101)+40);
        $p->addTitle('titleAnchor', '6', $this->t($p->title), '2x '.$this->t('from fabric'), ['scale' => 50, 'align' => 'left', 'rotate' => -90]);

        // Grain line
        $p->addPoint('grainlineTop', $p->shift(-100101,-45,10));
        $p->newPoint('grainlineBottom', $p->x('grainlineTop'), $p->y(41));
        $p->newGrainline('grainlineBottom', 'grainlineTop', $this->t('Grainline'));

        // Seam allowance
        if($this->o('sa')) $p->offsetPath('sa', 'outline', $this->o('sa')*-1, true, ['class' => 'fabric sa']);
    }

    /**
     * Finalizes the fly shield
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeFlyShield($model)
    {
        /** @var Part $p */
        $p = $this->parts['flyShield'];

        // Title
        $p->newPoint('titleAnchor', $p->x('leftTop')+30, $p->y(-100101)+40);
        $p->addTitle('titleAnchor', '7', $this->t($p->title), '1x '.$this->t('from lining'), ['scale' => 50, 'align' => 'left', 'rotate' => -90]);

        // Grain line
        $p->addPoint('grainlineTop', $p->shift('leftTop',-45,10));
        $p->newPoint('grainlineBottom', $p->x('grainlineTop'), $p->y(41));
        $p->newGrainline('grainlineBottom', 'grainlineTop', $this->t('Grainline'));

        // Seam allowance
        if($this->o('sa')) $p->offsetPath('sa', 'outline', $this->o('sa')*-1, true, ['class' => 'lining sa']);
    }

    /**
     * Finalizes the side piece
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeSidePiece($model)
    {
        /** @var Part $p */
        $p = $this->parts['sidePiece'];

        // Title
        $p->addPoint('titleAnchor', $p->shift(60,-75, 50));
        $p->addTitle('titleAnchor', '8', $this->t($p->title), '2x '.$this->t('from fabric'), ['scale' => 50, 'align' => 'left', 'rotate' => -90]);

        // Grain line
        $p->addPoint('grainlineTop', $p->shift(-1102,-135, 10));
        $p->newPoint('grainlineBottom', $p->x('grainlineTop'), $p->y(61)-7);
        $p->newGrainline('grainlineBottom', 'grainlineTop', $this->t('Grainline'));

        // Seam allowance
        if($this->o('sa')) $p->offsetPath('sa', 'outline', $this->o('sa')*-1, true, ['class' => 'fabric sa']);
    }

    /**
     * Finalizes the front pocket bag
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeFrontPocketBag($model)
    {
        /** @var Part $p */
        $p = $this->parts['frontPocketBag'];

        // Title
        $p->newPoint('titleAnchor', $p->x(60)-50 , $p->y(60) + 60);
        $p->addTitle('titleAnchor', 9, $this->t($p->title), '2x2 '.$this->t('from lining'));
        
        // Logo
        $p->addPoint('logoAnchor', $p->shift('titleAnchor',-90,90));
        $p->newSnippet('logo', 'logo-sm', 'logoAnchor');

        // Grain line
        $p->newPoint('grainlineTop', $p->x(810), $p->y(60) + 10);
        $p->newPoint('grainlineBottom', $p->x('grainlineTop'), $p->y(810)-10);
        $p->newGrainline('grainlineBottom', 'grainlineTop', $this->t('Grainline'));

        // Seam allowance
        if($this->o('sa')) $p->offsetPath('sa', 'outline', $this->o('sa'), true, ['class' => 'lining sa']);
    }

    /**
     * Finalizes the back inner pocket bag
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeBackInnerPocketBag($model)
    {
        /** @var Part $p */
        $p = $this->parts['backInnerPocketBag'];

        // Title
        $p->newPoint('titleAnchor',0,100);
        $p->addTitle('titleAnchor', 10, $this->t($p->title), '2x '.$this->t('from lining'), ['scale' => 75]);

        // Logo
        $p->addPoint('logoAnchor', $p->shift('titleAnchor',-90,60));
        $p->newSnippet('logo', 'logo-sm', 'logoAnchor');

        // Grain line
        $p->newPoint('grainlineTop', 40, 10);
        $p->newPoint('grainlineBottom', 40, 220);
        $p->newGrainline('grainlineBottom', 'grainlineTop', $this->t('Grainline'));
        
        // Seam allowance
        if($this->o('sa')) $p->offsetPath('sa', 'outline', $this->o('sa')*-1, true,['class' => 'lining sa']);

        // Notches
        $p->notch([5,6]);
    }

    /**
     * Finalizes the back outer pocket bag
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeBackOuterPocketBag($model)
    {
        /** @var Part $p */
        $p = $this->parts['backOuterPocketBag'];

        // Title
        $p->newPoint('titleAnchor',0,100);
        $p->addTitle('titleAnchor', 11, $this->t($p->title), '2x '.$this->t('from lining'), ['scale' => 75]);

        // Logo
        $p->addPoint('logoAnchor', $p->shift('titleAnchor',-90,60));
        $p->newSnippet('logo', 'logo-sm', 'logoAnchor');

        // Grain line
        $p->newPoint('grainlineTop', 40, 10);
        $p->newPoint('grainlineBottom', 40, 220);
        $p->newGrainline('grainlineBottom', 'grainlineTop', $this->t('Grainline'));
        
        // Seam allowance
        if($this->o('sa')) $p->offsetPath('sa', 'outline', $this->o('sa')*-1, true,['class' => 'lining sa']);

        // Notches
        $p->notch([5,6]);
    }

    /**
     * Finalizes the back pocket facing 
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeBackPocketFacing($model)
    {
        /** @var Part $p */
        $p = $this->parts['backPocketFacing'];

        // Title
        $p->newPoint('titleAnchor', 0, 70);
        $p->addTitle('titleAnchor', 12, $this->t($p->title), '4x '.$this->t('from fabric'), ['scale' => 75]);

        // Grain line
        $p->newPoint('grainlineTop', 50,10);
        $p->newPoint('grainlineBottom', 50,80);
        $p->newGrainline('grainlineBottom', 'grainlineTop', $this->t('Grainline'));

        // Notches
        $p->notch([5,6]);
    }

    /**
     * Finalizes the back pocket interfacing 
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeBackPocketInterfacing($model)
    {
        /** @var Part $p */
        $p = $this->parts['backPocketInterfacing'];

        // Title
        $p->newPoint('titleAnchor', 0, 50);
        $p->addTitle('titleAnchor', 13, $this->t($p->title), '4x '.$this->t('from interfacing'), ['scale' => 75]);

        // Grain line
        $p->newPoint('grainlineTop', 50,5);
        $p->newPoint('grainlineBottom', 50,65);
        $p->newGrainline('grainlineBottom', 'grainlineTop', $this->t('Grainline'));

        // Notches
        $p->notch([5,6]);
    }

    /**
     * Finalizes the belt loop 
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeBeltLoop($model)
    {
        /** @var Part $p */
        $p = $this->parts['beltLoop'];

        // Title
        $p->newPoint('titleAnchor', 10, 40);
        $p->addTitle('titleAnchor', 14, $this->t($p->title), '8x '.$this->t('from fabric'), ['scale' => 40, 'rotate' => -90, 'align' => 'left']);

        // Grain line
        $p->newPoint('grainlineTop', 10,5);
        $p->newPoint('grainlineBottom', 10,$p->y(2)-5);
        $p->newGrainline('grainlineBottom', 'grainlineTop', $this->t('Grainline'));

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
     * Adds paperless info for the back block
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessBack($model)
    {
        /** @var Part $p */
        $p = $this->parts['back'];

        // Dart
        $p->newPath('dartCenter', 'M dartTopLeft L dartTopRight M dartTop L dartTip', ['class' => 'helpline']);
        $p->newLinearDimension('dartTip', 'dartTop', $p->distance('dartTop','dartTopRight')+15); // Dart length
        $p->newLinearDimension('dartTopLeft', 'dartTopRight', 20); // Dart width
        $p->newLinearDimension(66601, 'dartTop', -20); // Dart placement

        // Pocket
        $p->newLinearDimension('pocketCenterLeft', 'dartTopLeft', -15); // Pocket height
        $p->newLinearDimension('pocketEdgeLeft', 'pocketCenterLeft', 25); // Pocket width left side
        $p->newLinearDimension('pocketCenterRight', 'pocketEdgeRight', 25); // Pocket width right side

        // Waist
        $p->newLinearDimension(66601, -2104, -35); // Waist seam length
        $p->newWidthDimension(66601, -2104, $p->y(66601)-50); // Waist horizontal
        $p->newHeightDimension(-2104,66601,$p->x(-2104)+25); // Waist vertical
        
        // Cross seam
        $p->newWidthDimension(-2301,66601, $p->y(66601)-50); // Crossseam horizontal
        $p->newCurvedDimension('M -2301 C -2301 -901603 -901601 C -901602 -901901 -9019', 25); // Cross seam lenght, curved part
        $p->newWidthDimension(-9019,66601); // Crossseam linear part, width
        $p->newHeightDimension(-9019,66601); // Crossseam linear part, height
        $p->newHeightDimension(-2301,66601, $p->x(-2301)-25); // Crossseam height

        // Inseam
        $p->newCurvedDimension('M -28 C -2702 -30 -30 C -3001 -2301 -2301', 25); // Inseam length
        $p->newHeightDimension(-28,-2301, $p->x(-2301)-40); // Inseam height
        $p->newWidthDimension(-2301,-28, $p->y(-28)+80); // Inseam width

        // Outseam
        $p->newCurvedDimension('M -27 C -2701 -29 -29 C -2901 -2601 -26 L -2104', -25); // Outseam length
        $p->newHeightDimension(-27,-2104, $p->x(-2104)+40); // Outseam height
        $p->newWidthDimension(-27,-2104, $p->y(-27)+80); // Outseam width

        // Hem
        $p->newWidthDimension(-28,-27, $p->y(-27)+80); // Leg width
        $p->newHeightDimensionSm(201,-28, $p->x(201)+15); // Hem curve depth
        $p->newNote($p->newId(), -20110, $this->t("Hem\nallowance")." : ".$p->unit(60), 12, 30, 15,['dy' => -6, 'line-height' => 6]);
    }
    
    /**
     * Adds paperless info for the front block
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessFront($model)
    {
        /** @var Part $p */
        $p = $this->parts['front'];
        
        // Waist
        $p->newCurvedDimension('M -100101 C -1002 -1102 -1102', 25); // Waist seam length
        $p->newWidthDimension(-100101, -1102, $p->y(-1102)-40); // Waist horizontal length
        $p->newHeightDimensionSm(-100101,-1102, $p->x(-1002)+25); // Height of the waist slope

        // Fly
        $p->newWidthDimensionSm(-6,-100101, $p->y(-6)+40); // To fly curve, horizontal 
        $p->newHeightDimension(-6,-100101, $p->x(-6)-25); // To fly curve, vertical
        $p->newWidthDimension(-9,-100101, $p->y(-100101)-25); // Crotch curve width
        $p->newHeightDimension(-9,-100101, $p->x(-9)-25); // Crotch curve width

        // Inseam
        $p->newCurvedDimension('M -13 C -1301 -15 -15 C -1402 -9 -9', 25); // Inseam length
        $p->newHeightDimension(-13,-9, $p->x(-9)-40); // Inseam, vertical
        $p->newWidthDimension(-9,-13, $p->y(-13)+80); // Inseam, horizontal

        // Outseam
        $p->newCurvedDimension('M -12 C -1201 -14 -14 C -1401 -802 -8 C -801 -1102 -1102', -25); // Outseam length
        $p->newHeightDimension(-12,-1102, $p->x(-1102)+50); // Outseam, vertical
        $p->newWidthDimension(-12,-1102, $p->y(-12)+80); // Outseam, horizontal
        
        // Hip bump
        $p->newWidthDimensionSm(-1102,-8, $p->y(-8)); // Outseam, horizontal
        $p->newHeightDimension(-8,-1102, $p->x(-1102)-25); // Outseam, vertical

        // Hem
        $p->newWidthDimension(-13,-12, $p->y(-12)+80); // Leg width
        $p->addPoint('hemNoteAnchor', $p->shift('grainlineBottom',225, 35));
        $p->newNote($p->newId(), 'hemNoteAnchor', $this->t("Hem\nallowance")." : ".$p->unit(60), 12, 40, 5,['dy' => -6, 'line-height' => 6]);
    }
    
    /**
     * Adds paperless info for the waistband interfacing left
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessWaistbandInterfacingLeft($model)
    {
        /** @var Part $p */
        $p = $this->parts['waistbandInterfacingLeft'];
        
        // Size
        $p->newWidthDimension(3,2,$p->y(2)+15); // Width
        $p->newHeightDimension(2,1, $p->x(1)+15); // Length
    }
    
    /**
     * Adds paperless info for the waistband interfacing right
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessWaistbandInterfacingRight($model)
    {
        /** @var Part $p */
        $p = $this->parts['waistbandInterfacingRight'];
        
        // Size
        $p->newWidthDimension(3,2,$p->y(2)+15); // Width
        $p->newHeightDimension(2,1, $p->x(1)+15); // Length
    }
    
    /**
     * Adds paperless info for the waistband left
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessWaistbandLeft($model)
    {
        /** @var Part $p */
        $p = $this->parts['waistbandLeft'];
        
        // Size
        $p->newWidthDimension(3,2,$p->y(2)+25); // Width
        $p->newHeightDimension(2,1, $p->x(1)+25); // Length
    }
    
    /**
     * Adds paperless info for the waistband right
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessWaistbandRight($model)
    {
        /** @var Part $p */
        $p = $this->parts['waistbandRight'];
        
        // Size
        $p->newWidthDimension(3,2,$p->y(2)+25); // Width
        $p->newHeightDimension(2,1, $p->x(1)+25); // Length
    }
    
    /**
     * Adds paperless info for the waistband lining left
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessWaistbandLiningLeft($model)
    {
        /** @var Part $p */
        $p = $this->parts['waistbandLiningLeft'];
        
        // Size
        $p->newWidthDimension(3,2,$p->y(2)+25); // Width
        $p->newHeightDimension(2,1, $p->x(1)+25); // Length
    }
    
    /**
     * Adds paperless info for the waistband lining right
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessWaistbandLiningRight($model)
    {
        /** @var Part $p */
        $p = $this->parts['waistbandLiningRight'];
        
        // Size
        $p->newWidthDimension(3,2,$p->y(2)+25); // Width
        $p->newHeightDimension(2,1, $p->x(1)+25); // Length
    }
    
    /**
     * Adds paperless info for the flyPiece
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessFlyPiece($model)
    {
        /** @var Part $p */
        $p = $this->parts['flyPiece'];
        
        // Seam length fly side
        $p->newCurvedDimension('M 43 C fly7 fly6 -6 L waistFly1', 25);

        // Width
        $p->newWidthDimension('waistFly1', -40, $p->y(-40)-25);

        // Note to trace from front
        $front = $this->parts['front'];
        $p->newNote($p->newId(), 'grainlineBottom', $p->unit($p->distance('waistFly1',-40)).' '.$this->t("wide")."\n".$this->t("Trace shape from").' '.$this->t($front->getTitle()), 6, 40, 5);
        // Fixme: Notes don't extend the bounding box (yet) 
        // so let's draw an invisible path to prevent the note from being cropped
        $p->addPoint('noteCropBust', $p->shift('grainlineBottom', -90, 60)); 
        $p->newPath($p->newId(), 'M grainlineBottom L noteCropBust', ['class' => 'hidden']); 
    }
    
    /**
     * Adds paperless info for the flyShield
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessFlyShield($model)
    {
        /** @var Part $p */
        $p = $this->parts['flyShield'];

        // Width
        $p->newWidthDimension('leftTop', -40, $p->y(-40)-25);

        // Note to trace from front
        $flyPiece = $this->parts['flyPiece'];
        $p->newNote($p->newId(), 'grainlineBottom', $p->unit($p->distance('leftTop',-40)).' '.$this->t("wide")."\n".$this->t("Trace shape from").' '.$this->t($flyPiece->getTitle()), 6, 40, 5);
        // Fixme: Notes don't extend the bounding box (yet) 
        // so let's draw an invisible path to prevent the note from being cropped
        $p->addPoint('noteCropBust', $p->shift('grainlineBottom', -90, 60)); 
        $p->newPath($p->newId(), 'M grainlineBottom L noteCropBust', ['class' => 'hidden']); 
    }
    
    /**
     * Adds paperless info for the sidePiece
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessSidePiece($model)
    {
        /** @var Part $p */
        $p = $this->parts['sidePiece'];
        
        // Width
        $p->newWidthDimension('topLeft', -1102, $p->y(-1102)-25);
        $p->newWidthDimension('bottomLeft', 61, $p->y(61)+25);
        
        // Height
        $p->newHeightDimension(61, -1102, $p->x(61)+35);

        // Note to trace from front
        $front = $this->parts['front'];
        $p->addPoint('noteAnchor', $p->shift('bottomLeft',90,30));
        $p->newNote($p->newId(), 'noteAnchor', $this->t("Trace shape from").' '.$this->t($front->getTitle()), 12, 15, 0);
    }

    /**
     * Adds paperless info for the front pocket bag
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessFrontPocketBag($model)
    {
        /** @var Part $p */
        $p = $this->parts['frontPocketBag'];
        
        // Width top
        $p->newWidthDimension(-100101, -40, $p->y(-1102)-25);
        $p->newWidthDimension(-40,'grainlineTop', $p->y(-1102)-25);
        $p->newWidthDimension('grainlineTop',-1102, $p->y(-1102)-25);

        // Height left
        $p->newHeightDimension(813,-100101, $p->x(813)-25);
        $p->newHeightDimension(810,-1102, $p->x(-1102)+35);
        
        // Width bottom
        $p->newWidthDimension(810,-1102, $p->y(810)+25);

        // Note
        $front = $this->parts['front'];
        $p->newNote($p->newId(), 'titleAnchor', $this->t("Match shape to").' '.$this->t($front->getTitle()), 6, 55, 30);
    }

    /**
     * Adds paperless info for the back inner pocket bag
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessBackInnerPocketBag($model)
    {
        /** @var Part $p */
        $p = $this->parts['backInnerPocketBag'];
        
        // Size
        $p->newWidthDimension(33,23,$p->y(23)+25); // Width
        $p->newHeightDimension(21,1, $p->x(1)+25); // Length

        // Welt
        $p->newHeightDimension(5,1,$p->x(5)+15); // From top
        $p->newWidthDimension(8,10,$p->y(10)-15); // Width
    }

    /**
     * Adds paperless info for the back inner pocket bag
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessBackOuterPocketBag($model)
    {
        /** @var Part $p */
        $p = $this->parts['backOuterPocketBag'];
        
        // Size
        $p->newWidthDimension(33,23,$p->y(23)+25); // Width
        $p->newHeightDimension(21,1, $p->x(1)+25); // Length

        // Welt
        $p->newHeightDimension(5,1,$p->x(5)+15); // From top
        $p->newWidthDimension(8,10,$p->y(10)-15); // Width
    }

    /**
     * Adds paperless info for the back pocket facing
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessBackPocketFacing($model)
    {
        /** @var Part $p */
        $p = $this->parts['backPocketFacing'];
        
        // Size
        $p->newWidthDimension(3,2,$p->y(2)+25); // Width
        $p->newHeightDimension(2,1, $p->x(1)+25); // Length

        // Welt
        $p->newHeightDimension(5,1,$p->x(5)+15); // From top
        $p->newWidthDimension(8,10,$p->y(10)-20); // Width
    }

    /**
     * Adds paperless info for the back pocket facing
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessBackPocketInterfacing($model)
    {
        /** @var Part $p */
        $p = $this->parts['backPocketInterfacing'];
        
        // Size
        $p->newWidthDimension(3,2,$p->y(2)+25); // Width
        $p->newHeightDimension(2,1, $p->x(1)+25); // Length

        // Welt
        $p->newHeightDimension(5,1,$p->x(5)+15); // From top
        $p->newWidthDimension(8,10,$p->y(10)-20); // Width
    }

    /**
     * Adds paperless info for the belt loop
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessBeltLoop($model)
    {
        /** @var Part $p */
        $p = $this->parts['beltLoop'];
        
        // Size
        $p->newWidthDimension(3,2,$p->y(2)+45); // Width
        $p->newHeightDimension(2,1, $p->x(1)+15); // Length
    }
}
