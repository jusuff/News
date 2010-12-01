<?php

class News_ImageUtil {

    /**
     * delete the three related pictures for the given name
     *
     * @author msshams
     * @author craigh
     * @param string $dir directory location of files without trailing slash
     * @param array $names array of filenames
     * @return int
     */
    public static function deleteImagesByName($dir, $names)
    {
        $count = 0;
        foreach ($names as $name) {
            @unlink($dir . DIRECTORY_SEPARATOR . $name . "-norm.jpg");
            @unlink($dir . DIRECTORY_SEPARATOR . $name . "-thumb.jpg");
            @unlink($dir . DIRECTORY_SEPARATOR . $name . "-thumb2.jpg");
            $count++;
        }
        //LogUtil::registerStatus(__f('Images deleted by name (%s)', $count, ZLanguage::getModuleDomain('News')));
        return $count;
    }

    /**
     * delete all the pictures for a given story ID
     *
     * @author msshams
     * @author craigh
     * @param string $dir
     * @param integer $sid
     * @param integer $count
     * @return integer number of images deleted
     */
    public static function deleteImagesBySID($dir, $sid, $count)
    {
        $result = 0;
        for ($i=0; $i<$count; $i++) {
            $name = "pic_sid" . $sid . "-" . $i;
            $result += self::deleteImagesByName($dir, array($name));
        }
        //LogUtil::registerStatus(__f('Total images deleted by SID (%s)', $count, ZLanguage::getModuleDomain('News')));
        return $result;
    }

    /**
     * Resize all the picture files in the $files array
     * and save to chosen directory
     *
     * @author msshams
     * @author craigh
     * @param int $sid story ID
     * @param array $files
     * @param array $modvars
     * @return int number of files resized
     */
    public static function resizeImages($sid, $files, $modvars, $index = 0)
    {
        // include the phpthumb library for thumbnail generation
        require_once ('modules/News/lib/vendor/phpthumb/ThumbLib.inc.php');
        $uploaddir = $modvars['picupload_uploaddir'] . DIRECTORY_SEPARATOR;
        $method = ($modvars['picupload_sizing'] == '0') ? 'Resize' : 'adaptiveResize';
        foreach ($files as $key => $file) {
            if ($file['resize']) {
                $filename = $uploaddir . 'pic_sid' . $sid . '-' . $index;
                // TODO loop this? add try/catch block
                $thumb1 = PhpThumbFactory::create($file['tmp_name'], array('jpegQuality' => 80));
                $thumb1->$method($modvars['picupload_picmaxwidth'], $modvars['picupload_picmaxheight']);
                $thumb1->save($filename . '-norm.jpg', 'jpg');
                $thumb2 = PhpThumbFactory::create($file['tmp_name'], array('jpegQuality' => 80));
                $thumb2->$method($modvars['picupload_thumbmaxwidth'], $modvars['picupload_thumbmaxheight']);
                $thumb2->save($filename . '-thumb.jpg', 'jpg');
                if ($index == 0) {
                    $thumb3 = PhpThumbFactory::create($file['tmp_name'], array('jpegQuality' => 80));
                    $thumb3->$method($modvars['picupload_thumb2maxwidth'], $modvars['picupload_thumb2maxheight']);
                    $thumb3->save($filename . '-thumb2.jpg', 'jpg');
                }
                $index++;
            }
        }
        //LogUtil::registerStatus(__f('Images resized (%s)', $index, ZLanguage::getModuleDomain('News')));
        return $index;
    }

    /**
     * Count the valid uploaded pictures and mark them as resizable
     * convert status of news item to DRAFT if unsuccessful upload
     *
     * @author msshams
     * @author craigh
     * @param array $files
     * @param array $item
     * @param array $modvars
     * @return array ($files, $item)
     */
    public static function validateImages($files, $item, $modvars)
    {

        $uploadNoFile = false;
        $count = 0;

        $allowedExtensionsArray = explode(',', $modvars['picupload_allowext']);
        foreach ($files as $key => $file) {
            $files[$key]['resize'] = false; // set default to no resize
            if ($file['size'] > $modvars['picupload_maxfilesize']) {
                $file['error'] = UPLOAD_ERR_FORM_SIZE;
            }
            $dom = ZLanguage::getModuleDomain('News');
            switch ($file['error']) {
                case UPLOAD_ERR_FORM_SIZE:
                    $uploadNoFile = true;
                    LogUtil::registerStatus(__f('Warning! Picture %s is not uploaded, since the filesize was too large (max. %s kB).', array($key+1, $modvars['picupload_maxfilesize']/1000), $dom));
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $uploadNoFile = true;
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_PARTIAL:
                case UPLOAD_ERR_NO_TMP_DIR:
                case UPLOAD_ERR_CANT_WRITE:
                case UPLOAD_ERR_EXTENSION:
                    $uploadNoFile = true;
                    LogUtil::registerStatus(__f('Warning! Picture %1$s gave an error (code %2$s, explained on this page: %3$s) during uploading.', array($key+1, $error, 'http://php.net/manual/features.file-upload.errors.php'), $dom));
                    break;
                case UPLOAD_ERR_OK:
                    $file_extension = FileUtil::getExtension($file['name']);
                    if (!in_array(strtolower($file_extension), $allowedExtensionsArray) && !in_array(strtoupper(($file_extension)), $allowedExtensionsArray)) {
                        LogUtil::registerStatus(__f('Warning! Picture %s is not uploaded, since the file extension is now allowed (only %s is allowed).', array($key+1, $modvars['picupload_allowext']), $dom));
                    } else {
                        $files[$key]['resize'] = true;
                        $count++;
                    }
                    break;
            }
        }
        $item['pictures'] = $count;

        // make the article draft when there is an upload error and ADD permission is present
        // TODO why only for ADD ???
        if (($uploadNoFile) && SecurityUtil::checkPermission('News::', '::', ACCESS_ADD)) {
            $item['action'] = 6;
        }

        return array($files, $item);
    }

    /**
     * renumber the remaining files if files were deleted
     * 
     * @author msshams
     * @author craigh
     * @param int $numOfPics number of images in the original story
     * @param int $sid the story ID
     * @param array $modvars
     * @return int ID of last file renamed
     */
    public static function renumberImages($numOfPics, $sid, $modvars) {
        $uploaddir = $modvars['picupload_uploaddir'] . DIRECTORY_SEPARATOR;
        $lastfile = 0;
        for ($i=0; $i<$numOfPics; $i++){
            $filename = 'pic_sid' . $sid . "-";
            if (file_exists($uploaddir . $filename . $i . "-norm.jpg")) {
                rename($uploaddir . $filename . $i . "-norm.jpg", $uploaddir . $filename . $lastfile . "-norm.jpg");
                rename($uploaddir . $filename . $i . "-thumb.jpg", $uploaddir . $filename . $lastfile . "-thumb.jpg");
                rename($uploaddir . $filename . $i . "-thumb2.jpg", $uploaddir . $filename . $lastfile . "-thumb2.jpg");
                // create a new hometext image if needed
                if ($lastfile == 0 && !file_exists($uploaddir . $filename . $lastfile . "-thumb2.jpg")) {
                    require_once ('modules/News/lib/vendor/phpthumb/ThumbLib.inc.php');
                    $method = ($modvars['picupload_sizing'] == '0') ? 'Resize' : 'adaptiveResize';
                    $thumb2 = PhpThumbFactory::create($uploaddir . $filename . $lastfile . "-norm.jpg", array('jpegQuality' => 80));
                    $thumb2->$method($modvars['picupload_thumb2maxwidth'],$modvars['picupload_thumb2maxheight']);
                    $thumb2->save($uploaddir . $filename . $lastfile . '-thumb2.jpg', 'jpg');
                }
                $lastfile++;
            }
        }
        //LogUtil::registerStatus(__f('Images renumbered (%s)', $lastfile, ZLanguage::getModuleDomain('News')));
        return $lastfile;
    }

    /**
     * re-array the $_FILES array to be key-based instead of info-based
     * 
     * @link http://www.php.net/manual/en/features.file-upload.multiple.php#53240
     * @param array $file_post $_FILES array to reArray
     * @return array resultant reordered array
     */

    public static function reArrayFiles($file_post) {
        if (!isset($file_post) || !is_array($file_post)) {
            return null;
        }
        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);
        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }
        //filter out empty values
        foreach ($file_ary as $k => $f) {
            if (empty($f['name'])) {
                unset($file_ary[$k]);
            }
        }
        return $file_ary;
    }
}