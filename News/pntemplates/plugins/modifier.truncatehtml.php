<?php
/**
* Zikula Application Framework
*
* @copyright (c) 2004, Zikula Development Team
* @link http://www.zikula.org
* @version $Id:  $
* @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
* @package Zikula_Template_Plugins
* @subpackage Modifiers
*/

/**
* Smarty modifier to truncate a string preserving any html tag nesting and matching
*
* Original code from http://phpinsider.com/smarty-forum/viewtopic.php?t=533 
*   Author: Original Javascript Code: Benjamin Lupu <lupufr@aol.com>
*   Translation to PHP & Smarty: Edward Dale <scompt@scompt.com>
*   Modification to add a string: Sebastian Kuhlmann <sebastiankuhlmann@web.de>

* The plugin truncates a string preserving any html tag nesting and matching. The 
* string is truncated on whole words as well. An optional suffix is added when given
* as parameter.
*
* Example
*   <!--[$myvar|truncatehtml:100:'...']-->
* 
* @author       Erik Spaan [espaan]
* @since        30/09/2008
* @param        array    $string     The contents to transform
* @param        int      $length     The truncated string length
* @param        string   $suffix     Optional suffix that will only be added if the string is truncated
* @return       string   the modified output
*/
function smarty_modifier_truncatehtml($string, $length, $suffix="")
{
    if (strlen($string) > $length) {
        if (!empty($string) && $length>0) {
            $isText = true;
            $ret = "";
            $i = 0;

            $currentChar = "";
            $lastSpacePosition = -1;
            $lastChar = "";

            $tagsArray = array();
            $currentTag = "";

            // String length without html tags
            $noTagLength = strlen(strip_tags($string));

            // Parser loop
            for ($j=0; $j<strlen($string); $j++) {

                $currentChar = substr($string, $j, 1);
                $ret .= $currentChar;

                // Lesser than event
                if ($currentChar == "<") {
                    $isText = false;
                }

                // Character handler
                if ($isText) {
                    // Memorize last space position
                    if ($currentChar == " ") {
                        $lastSpacePosition = $j;
                    } else {
                        $lastChar = $currentChar;
                    }
                    $i++;
                } else {
                    $currentTag .= $currentChar;
                }

                // Greater than event
                if ($currentChar == ">") {
                    $isText = true;

                    // Opening tag handler
                    if ((strpos($currentTag, "<") !== FALSE) &&
                            (strpos($currentTag, "/>") === FALSE) &&
                            (strpos($currentTag, "</") === FALSE)) {

                        // Tag has attribute(s)
                        if (strpos($currentTag, " ") !== FALSE) {
                            $currentTag = substr($currentTag, 1, strpos($currentTag, " ") - 1);
                        } else {
                            // Tag doesn't have attribute(s)
                            $currentTag = substr($currentTag, 1, -1);
                        }
                        array_push ($tagsArray, $currentTag);
                    } elseif (strpos($currentTag, "</") !== FALSE) {
                        array_pop($tagsArray);
                    }
                    $currentTag = "";
                }
                if ($i >= $length) {
                    break;
                }
            }

            // Cut HTML string at last space position
            if ($length < $noTagLength) {
                if ($lastSpacePosition != -1) {
                    $ret = substr($string, 0, $lastSpacePosition);
                } else {
                    $ret = substr($string, $j);
                }
            }

            // Close broken XHTML elements
            while (sizeof($tagsArray) != 0) {
                $aTag = array_pop($tagsArray);
                $ret .= "</" . $aTag . ">\n";
            }

        } else {
            $ret = "";
        }

        // Only add suffix if string was truncated
        if (strlen($string) > $length) {
            return ($ret . " " . $suffix);
        }
        else {
            return ($ret);
        }
    } else { // String not truncated
        return ($string);
    }
}