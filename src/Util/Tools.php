<?php
namespace App\Util;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class Tools
{
    public static function rmRecursive($path) {
        if (!file_exists($path)) {
            return false;
        }
        if (is_dir($path)) {
            $dir = dir($path);
            while (($file_in_dir = $dir->read()) !== false) {
                if ($file_in_dir == '.' or $file_in_dir == '..')
                    continue; // passage au tour de boucle suivant
                Tools::rmRecursive("$path/$file_in_dir");
            }
            $dir->close();
        } else {
            unlink($path);
        }
    }
    
    public static function copyFiles($src_dir, $dest_dir, $sym_link = false) {
        $fs = new Filesystem();
        if($fs->exists($src_dir)) {
            $fs->mkdir($dest_dir, 0755, true);
            $dir_iterator = new \RecursiveDirectoryIterator($src_dir, \RecursiveDirectoryIterator::SKIP_DOTS);
            $iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);
            foreach($iterator as $element){
               if($element->isDir()){
                    $fs->mkdir($dest_dir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
               } else{
                    if(!$fs->exists($dest_dir . DIRECTORY_SEPARATOR . $iterator->getSubPathName()))
                        if($sym_link)
                            $fs->symlink($element, $dest_dir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                        else
                            $fs->copy($element, $dest_dir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
               }
            }
        }
    }
    
    public static function copy($src_file, $dest_file, $sym_link = false) {
        $fs = new Filesystem();
        if($fs->exists($src_file)) {
            if($sym_link)
                $fs->symlink($src_file, $dest_file);
            else
                $fs->copy($src_file, $dest_file);
        }
    }
    
    public static function removeFile($file) {
        $fs = new Filesystem();
        if($fs->exists($file)) {
            $fs->remove($file);
            return true;
        }
        return false;
    }
    
    public static function removeFiles($ressources_path) {
        $fs = new Filesystem();

        if($fs->exists($ressources_path)) {
            $dir_iterator = new \RecursiveDirectoryIterator($ressources_path, \RecursiveDirectoryIterator::SKIP_DOTS);
            $iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);
            foreach($iterator as $element){
                if ($fs->exists($element) && !$element->isDir())
                    $fs->remove($element);
            }
            $fs->remove($ressources_path);
        }
    }
}

