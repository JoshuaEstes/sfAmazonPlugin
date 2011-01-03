<?php
/**
 * @package sfAmazonPlugin
 * @subpackage S3
 * @author Joshua Estes <Joshua.Estes@ScenicCityLabs.com>
 */
class sfValidatedAmazonS3File extends sfValidatedFile
{

  /**
   *
   * @param  string $file      The file path to save the file
   * @param  int    $fileMode  The octal mode to use for the new file
   * @param  bool   $create    Indicates that we should make the directory before moving the file
   * @param  int    $dirMode   The octal mode to use when creating the directory
   *
   * @return string The filename without the $this->path prefix
   *
   * @throws Exception
   */
  public function save($file = null, $fileMode = 0666, $create = true, $dirMode = 0777)
  {
    if (null === $file)
      $file = $this->generateFilename ();

    if (0 !== strpos($file,'/'))
    {
      if (null === $this->path)
        throw new RuntimeException('You must give a "path" when you give a relative file name.');

      $file = $this->path.'/'.$file;
    }

    $directory = diname($file);

    @mkdir($directory, $dirMode, true);
    @chmod($file, $fileMode);

    $this->savedName = $file;

    return null === $this->path ? $file : str_replace($this->path.'/','',$file);

  }

}