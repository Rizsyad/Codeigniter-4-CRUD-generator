<?php

class File
{
    private $path;

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function isPermissionWritable($folder)
    {
        $folder = $this->path . "/" . $folder;
        $is_writable = file_put_contents("$folder/dummy-MVC-CodeIgniter-4.txt", "Dummy Check Permission");

        @unlink("$folder/dummy-MVC-CodeIgniter-4.txt");
        return ($is_writable > 0);
    }

    public function createFile($file, $content)
    {
        return file_put_contents($file, $content);
    }
}
