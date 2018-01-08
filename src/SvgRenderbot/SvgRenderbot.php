<?php
/** Freesewing\SvgRenderbot */
namespace Freesewing;

/**
 * Renders a pattern into SVG.
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2016-2017 Joost De Cock
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, Version 3
 */
class SvgRenderbot
{
    /** @var string TAB In the holy war of tab vs spaces, we're on the spaces side. Richard Hendricks be damned. */
    const TAB = '    ';

    /** @var int $tabs Keeps track of how many times we need to indent */
    private $tabs = 0;

    /** @var int $freeId Counter to give as an unused ID */
    private $freeId = 1;

    /** @var array $openGroups Where we keep track of currently opened groups */
    private $openGroups = array();

    /**
     * Returns SVG code for a pattern
     *
     * This does not return the entire SVG document, but merely the SVG body
     * Also note that this mostly just calls renderPart() on all parts
     * that need to be rendered.
     *
     * @param \Freesewing\Pattern or equivalent. The pattern to render
     *
     * @return string The SVG code for the pattern
     */
    public function render($pattern)
    {
        $svg = $this->openGroup('patternContainer');

        if (isset($pattern->parts) && count($pattern->parts) > 0) :
            foreach ($pattern->parts as $partKey => $part) {
                if ($part->getRender() === true) {
                    $transforms = '';
                    if (is_array($part->transforms) && count($part->transforms) > 0) {
                        $transforms = \Freesewing\Transform::asSvgParameter($part->transforms);
                    }

                    $svg .= $this->openGroup($partKey, $transforms);
                    $svg .= $this->renderPart($part);
                    $svg .= $this->closeGroup();
                }
            }
        endif;

        $svg .= $this->closeGroup();

        return $svg;
    }

    /**
     * Returns SVG code for a path
     *
     * @param \Freesewing\Path $path The path to render
     * @param \Freesewing\Part $part The part this path is part of (did you get that?)
     *
     * @return string The SVG code for the rendered path
     */
    public function renderPath($path, $part)
    {
        if ($path->getRender() === false) {
            return '';
        }
        $pathstring = $path->getPathstring();
        $points = $part->points;
        $patharray = Utils::asScrubbedArray($pathstring);
        $svg = '';
        foreach ($patharray as $p) {
            $p = rtrim($p);
            if ($p != '' && Utils::isAllowedPathCommand($p)) {
                $svg .= " $p ";
            } elseif (is_object($points[$p])) {
                $svg .= ' '.$points[$p]->x.','.$points[$p]->y.' ';
            }
        }
        $attributes = $path->getAttributes();
        if (!isset($attributes['id'])) {
            $attributes['id'] = $this->getUid();
        }

        return $this->nl().'<path '.Utils::flattenAttributes($attributes).' d="'.$svg.'" />';
    }

    /**
     * Returns indentation
     *
     * @return string
     */
    private function tab()
    {
        $space = '';
        for ($i = 0; $i < $this->tabs; ++$i) {
            $space .= self::TAB;
        }

        return $space;
    }

    /**
     * Returns a linebreak + indentation
     *
     * @return string
     */
    private function nl()
    {
        return "\n".$this->tab();
    }

    /**
     * Increases indentation by 1
     */
    private function indent()
    {
        $this->tabs += 1;
    }

    /**
     * Decreases indentation by 1
     */
    private function outdent()
    {
        $this->tabs -= 1;
    }

    /**
     * Returns an unused ID
     *
     * @return int The id
     */
    private function getUid()
    {
        $this->freeId += 1;

        return $this->freeId;
    }

    /**
     * Returns SVG code to open a group
     *
     * Apart from returning the SVG code to open a group
     * this will also push this group on the opengroups array
     * and call indent() so that what follows will be more indented
     *
     * @param string|false ID to use for the group, or false to use an auto-generated ID
     * @param string $options A string to include in the g tag (could be attributes)
     *
     * @return string The SVG code to open the group
     */
    private function openGroup($id, $options = null)
    {
        $svg = $this->nl();
        $svg .= $this->nl().
            "<!-- Start of group #$id -->".$this->nl().
            "<g id=\"$id\" $options>";
        $this->indent();
        array_push($this->openGroups, $id);

        return $svg;
    }

    /**
     * Returns SVG code to close a group
     *
     * Apart from returning the SVG code to close a group
     * this will also remove this group from the opengroups array
     * and call outdent()
     *
     * @return string The SVG code to close the group
     */
    private function closeGroup()
    {
        $id = array_pop($this->openGroups);
        $this->outdent();

        return $this->nl().'</g>'.$this->nl()."<!-- End of group #$id -->";
    }

    /**
     * Returns SVG code for a pattern part
     *
     * This renders the following elements contained within the part:
     *  - includes
     *  - paths
     *  - snippets
     *  - texts
     *  - textsonpath
     *  - notes
     *  - dimensions
     *
     * @param \Freesewing\Part The part to render
     *
     * @return The SVG code for the part
     */
    private function renderPart($part)
    {
        $svg = '';
        if ($part->paths) {
            foreach ($part->paths as $path) {
                $svg .= $this->renderPath($path, $part);
            }
        }
        if ($part->includes) {
            foreach ($part->includes as $include) {
                $svg .= $include->get();
            }
        }
        if ($part->textsOnPath) {
            foreach ($part->textsOnPath as $textOnPath) {
                $svg .= $this->renderTextOnPath($textOnPath, $part);
            }
        }
        if ($part->notes) {
            foreach ($part->notes as $note) {
                $svg .= $this->renderNote($note, $part);
            }
        }
        if ($part->dimensions) {
            foreach ($part->dimensions as $dimension) {
                $svg .= $this->renderDimension($dimension, $part);
            }
        }
        if ($part->texts) {
            foreach ($part->texts as $text) {
                $svg .= $this->renderText($text, $part);
            }
        }
        if ($part->snippets) {
            foreach ($part->snippets as $snippet) {
                $svg .= $this->renderSnippet($snippet, $part);
            }
        }

        return $svg;
    }

    /**
     * Returns SVG code for a snippet
     *
     * @param \Freesewing\Snippet $snippet The snippet to render
     * @param \Freesewing\Part $part The part this snippet is part of
     *
     * @return string The SVG code for the rendered snippet
     */
    private function renderSnippet($snippet, $part)
    {
        $anchor = $snippet->getAnchor();
        $attributes = $snippet->getAttributes();
        $svg = $this->nl();
        $svg .= '<use x="'.$anchor->getX().'" y="'.$anchor->getY().'" xlink:href="#'.$snippet->getReference().'" ';
        if (!isset($attributes['id'])) {
            $svg .= 'id="'.$this->getUid().'" ';
        }
        $svg .= Utils::flattenAttributes($attributes);
        $svg .= '>';
        if($snippet->getDescription() != '') $svg .= '<title>'.$snippet->getDescription().'</title>';
        $svg .= '</use>';
        
        return $svg;
    }

    /**
     * Returns SVG code for text
     *
     * There's a special attribute 'line-height' that can be set in the
     * text attributes. If it's present, it will be used to set the offset
     * between lines of text, and somewhat imitating the line-heigt attribute
     * of HTML text (SVG has no such thing).
     *
     * To get multi-line text, just include linebreaks (\n) in your input text.
     *
     * @param \Freesewing\Text|\Freesewing\TextOnPath $text The Text or TextOnPath to render
     * @param \Freesewing\Part $part The part this text is part of
     *
     * @return string The SVG code for the rendered text
     */
    private function renderText($text, $part)
    {
        $anchor = $text->getAnchor();
        $svg = $this->nl();
        $svg .= '<text x="'.$anchor->getX().'" y="'.$anchor->getY().'" ';
        if (!isset($text->attributes['id'])) {
            $svg .= 'id="'.$this->getUid().'" ';
        }
        if (isset($text->attributes['line-height'])) {
            $lineHeight = $text->attributes['line-height'];
        } else {
            $lineHeight = 12;
        }
        if( isset($text->attributes['writing-mode'])) {
            $vertical = ($text->attributes['writing-mode'] == 'tb-rl');
        } else {
            $vertical = 0;
        }
        $svg .= Utils::flattenAttributes($text->getAttributes(), ['line-height']);
        $svg .= '>';

        $lines = explode("\n", $text->getText());
        $attr = '';
        foreach ($lines as $line) {
            $svg .= "<tspan $attr>$line</tspan>";
            if( $vertical )
                $attr = 'dx="-'.$lineHeight.'" y="'.$anchor->getY().'"';
            else
                $attr = 'x="'.$anchor->getX().'" dy="'.$lineHeight.'"';
        }
        $svg .= '</text>';

        return $svg;
    }

    /**
     * Returns SVG code for a note
     *
     * A note is just a path and a text wrapped into one.
     * So this renders one of each.
     *
     * @param \Freesewing\Note $note The note to render
     * @param \Freesewing\Part $part The part this text is part of
     *
     * @return string The SVG code for the rendered note
     */
    private function renderNote($note, $part)
    {
        $path = $note->getPath();
        $svg = $this->renderPath($path, $part);
        $svg .= $this->renderText($note, $part);

        return $svg;
    }

    /**
     * Returns SVG code for a dimension
     *
     * A dimension has a Path, a TextOnPath and optional leaders (which are
     * also paths). Se we just render them each
     *
     * @param \Freesewing\Dimension $dimension The dimension to render
     * @param \Freesewing\Part $part The part this dimension is part of
     *
     * @return string The SVG code for the rendered dimension
     */
    private function renderDimension($dimension, $part)
    {
        // Label
        $svg = $this->renderTextOnPath($dimension->getLabel(), $part);

        // Leaders
        $leaders = $dimension->getLeaders();
        if(is_array($leaders) && count($leaders)>0) {
            foreach($leaders as $leader) {
                $svg .= $this->renderPath($leader, $part);
            }
        }

        return $svg;
    }

    /**
     * Returns SVG code for a textOnPath
     *
     * There's a special attribute 'line-height' that can be set in the
     * text attributes. If it's present, it will be used to set the offset
     * between lines of text, and somewhat imitating the line-heigt attribute
     * of HTML text (SVG has no such thing).
     *
     * To get multi-line text, just include linebreaks (\n) in your input text.
     *
     * @param \Freesewing\TextOnPath $textOnPath The textOnPath to render
     * @param \Freesewing\Part $part The part this textOnPath is part of
     *
     * @return string The SVG code for the rendered textOnPath
     */
    private function renderTextOnPath($textOnPath, $part)
    {
        $path = $textOnPath->getPath();
        $id = $path->getAttribute('id');
        // Make sure path has an ID
        if(!$id || strlen($id) < 1) {
            $id = $this->getUid();
            $path->setAttribute('id', $id);
        }
        $svg = $this->renderPath($path, $part);
        $svg .= $this->nl();
        $svg .= '<text ';
        if (!isset($textOnPath->attributes['id'])) {
            $svg .= 'id="'.$this->getUid().'" ';
        }
        if (isset($textOnPath->attributes['line-height'])) {
            $lineHeight = $textOnPath->attributes['line-height'];
        } else {
            $lineHeight = 12;
        }
        $svg .= Utils::flattenAttributes($textOnPath->getAttributes(), ['line-height']);
        $svg .= '>';

        $svg .= "<textPath xlink:href=\"#$id\" startOffset=\"50%\">".
            '<tspan '.Utils::flattenAttributes($textOnPath->getAttributes()).'>'.$textOnPath->getText().'</tspan>'.
            '</textPath>';
        $svg .= '</text>';

        return $svg;
    }
}
