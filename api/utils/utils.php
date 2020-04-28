<?php
define("USERS_ROOT", dirname(dirname(__DIR__)).'\\users');

class Utils
{
    public static function create_dir($email){
        $path = USERS_ROOT.'/'.$email;
        if(!file_exists($path))
            mkdir($path);
        return USERS_ROOT;
    }

    public static function remove_dir($email) {
        $path = USERS_ROOT.'/'.$email;
        self::delete_files($path);
    }

    private static function delete_files($target){
        if(is_dir($target) === true){
            $content = scandir($target);
            unset($content[0], $content[1]);
            foreach ($content as $c => $contentName) {
                $current = $target.'/'.$contentName;
                $filetype = filetype($current);

                if($filetype == 'dir') {
                    self::delete_files($current);
                } else {
                    unlink($current);
                }

                unset($content[$c]);
            }
            rmdir($target);
        }
    }

    public static function load_image($path, $img_encoded, $img_name){
        list($_, $img_encoded) = explode(';', $img_encoded);
        list(, $img_encoded)   = explode(',', $img_encoded);
        $img_encoded = base64_decode($img_encoded);
        $img_path = $path.'/'.$img_name;
        file_put_contents($img_path, $img_encoded);
        return USERS_ROOT.$path.'/'.$img_name;
    }
}