<?php

use Aws\Api\Parser\Exception\ParserException;
use Aws\S3\Exception\S3Exception;

App::uses('AppModel', 'Model');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');


class Archive extends AppModel
{

  public $useTable = false; // This model does not use a database table

  public function __construct($id = false, $table = null, $ds = null)
  {
    parent::__construct($id, $table, $ds);
    $this->s3Client = $this->AwsSDK->createS3();
    $this->s3Client->registerStreamWrapper();
  }

  private function createCommitFolder($exercise_id, $user_email, $commit_time)
  {
    App::import('Model', 'Exercise');
    App::import('Model', 'Offering');
    $Exercise = new Exercise();
    $Offering = new Offering();
    $Exercise->id = $exercise_id;
    $offering_id = $Exercise->getOfferingId();
    $Offering->id = $offering_id;
    $course_id = $Offering->getCourseId();

    $folder = Configure::read('Upload.dir') . '/' . $course_id . '/' . $offering_id . '/' . $exercise_id . '/' . $user_email . '/' . $commit_time . '/';
    $dir = new Folder($folder, true, 0777);
    return $folder;
  }

  public function getExerciseCaseFilesFolder($exercise_case_id)
  {
    //FAZER
    App::import('Model', 'ExerciseCase');
    App::import('Model', 'Exercise');
    App::import('Model', 'Offering');
    $ExerciseCase = new ExerciseCase();
    $Exercise = new Exercise();
    $Offering = new Offering();
    $ExerciseCase->id = $exercise_case_id;
    $exercise_id = $ExerciseCase->getExerciseId();
    $Exercise->id = $exercise_id;
    $offering_id = $Exercise->getOfferingId();
    $Offering->id = $offering_id;
    $course_id = $Offering->getCourseId();

    $folder = Configure::read('Upload.dir') . "/inputfiles/" . $course_id . "/" . $offering_id . "/" . $exercise_id . "/" . $exercise_case_id . "/";
    return $folder;
  }

  public function getExerciseFilesFolder($exercise_id)
  {
    App::import('Model', 'Exercise');
    App::import('Model', 'Offering');
    $Exercise = new Exercise();
    $Offering = new Offering();
    $Exercise->id = $exercise_id;
    $offering_id = $Exercise->getOfferingId();
    $Offering->id = $offering_id;
    $course_id = $Offering->getCourseId();

    $folder = Configure::read('Upload.dir') . "/exercisefiles/" . $course_id . "/" . $offering_id . "/" . $exercise_id . "/";
    return $folder;
  }

  public function getCompilationFilesFolder($exercise_id)
  {
    App::import('Model', 'Exercise');
    App::import('Model', 'Offering');
    $Exercise = new Exercise();
    $Offering = new Offering();
    $Exercise->id = $exercise_id;
    $offering_id = $Exercise->getOfferingId();
    $Offering->id = $offering_id;
    $course_id = $Offering->getCourseId();

    $folder = Configure::read('Upload.dir') . "/compilationfiles/" . $course_id . "/" . $offering_id . "/" . $exercise_id . "/";
    return $folder;
  }

  public function getCommitFolder($commit_id)
  {
    App::import('Model', 'Commit');
    App::import('Model', 'Exercise');
    App::import('Model', 'Offering');
    $Commit = new Commit();
    $Exercise = new Exercise();
    $Offering = new Offering();
    $Commit->recursive = -1;
    $commit_info = $Commit->findById($commit_id, array('commit_time', 'exercise_id', 'user_email', 'id'));
    $Exercise->id = $commit_info['Commit']['exercise_id'];
    $offering_id = $Exercise->getOfferingId();
    $Offering->id = $offering_id;
    $course_id = $Offering->getCourseId();
    $folder = Configure::read('Upload.dir') . '/' . $course_id . '/' . $offering_id . '/' . $commit_info['Commit']['exercise_id'] . '/' . $commit_info['Commit']['user_email'] . '/' . $commit_info['Commit']['commit_time'] . '/';
    return $folder;
  }

  public function getOutputFolder($commit_id)
  {
    $folder = Configure::read('Upload.dir') . '/outputfiles/' . $commit_id . '/';
    return $folder;
  }

  public function getCommitFile($commit_id)
  {
    $folder = $this->getCommitFolder($commit_id);
    $dir = new Folder($folder, false, 0777);
    $files = $dir->find();

    if (count($files) > 0 && file_exists($folder . $files[0])) {
      return $folder . $files[0];
    } else {
      return false;
    }
  }

  public function getExerciseFile()
  {
    App::import('Model', 'ExerciseFile');
    App::import('Model', 'Exercise');
    App::import('Model', 'Offering');
  }

  public function saveCommitFile()
  {
  }

  public function saveExerciseFile()
  {
  }

  public function copyCommitFiletoAwsS3($file, $commit, $exercise)
  {
    $key = $exercise['Exercise']['id'] . DS . $commit['Commit']['id'] . "-" . $file["name"];
    $meta = array();
    if (strlen($exercise['Exercise']['offering_id']) > 0) $meta["Offering"] = $exercise['Exercise']['offering_id'];
    if (strlen($exercise['Exercise']['title']) > 0) $meta["ExerciseTitle"] = $this->_removeAccents($exercise['Exercise']['title']);
    if (strlen($commit['Commit']['id']) > 0) $meta["CommitId"] = $commit['Commit']['id'];
    if (strlen($commit['Commit']['commit_time']) > 0) $meta["CommitDateTime"] = $commit['Commit']['commit_time'];
    if (strlen($commit['Commit']['user_email']) > 0) $meta["User"] = $commit['Commit']['user_email'];
    try {
      $result = $this->s3Client->putObject(array(
        'Bucket' => Configure::read('AWS.commits-bucket-name'),
        'Key' => $key,
        'SourceFile' => $file["path"] . DS . $file["name"],
        //            'ContentType'  => 'text/plain',
        'ACL' => 'private',
        'StorageClass' => 'STANDARD',
        'Metadata' => $meta
      ));
      return $key;
    } catch (Exception $e) {
      App::import('Model', 'Message');
      $Message = new Message();
      //            Log::register("Archive.php[method: copyCommitFiletoAwsS3]".$e->getMessage());
      //            $Message->sendAlertEmail("Archive.php[method: copyCommitFiletoAwsS3]".$e->getMessage(),"Error on upload commit " .$commit['Commit']['id']. " to AWS S3");
      return false;
    }
  }

  public function getCommitFileContentFromAwsS3($awsKey)
  {
    $split = explode(".", $awsKey);
    $ext = $split[count($split) - 1];
    try {
      if ($ext != "zip" && $ext != "pdf" && $ext != "exe") {
        $result = $this->s3Client->getObject(array(
          'Bucket' => Configure::read('AWS.commits-bucket-name'),
          'Key'    => $awsKey
        ));
        return array("ext" => $ext, "body" => $result["Body"]);
      } else if ($ext == "zip") {
        $head = $this->s3Client->HeadObject(array(
          'Bucket' => Configure::read('AWS.commits-bucket-name'),
          'Key'    => $awsKey
        ));

        $cachedFile = $this->_cacheFile(Configure::read('AWS.commits-bucket-name'), $awsKey, $head->get('LastModified')->__toString());
        if ($cachedFile) {
          return array("ext" => $ext, "body" => $cachedFile);
        }
      } else if ($ext == "pdf") {
        return array("ext" => $ext, "body" => "");
      } else {
        return false;
      }
    } catch (Exception $e) {
      App::import('Model', 'Message');
      Log::registerException($e, "Archive.php", "copyCommitFiletoAwsS3");
      return false;
    }
    return false;
  }

  public function getCommitFileDownloadFromAwsS3($awsKey)
  {
    $split = explode(".", $awsKey);
    $ext = $split[count($split) - 1];
    try {
      $result = $this->s3Client->getObject(array(
        'Bucket' => Configure::read('AWS.commits-bucket-name'),
        'Key'    => $awsKey
      ));
      return array("ext" => $ext, "result" => $result);
    } catch (Exception $e) {
      App::import('Model', 'Message');
      Log::registerException($e, "Archive.php", "copyCommitFiletoAwsS3");
      return false;
    }
  }

  public function getCommitFileSavedFromAwsS3($awsKey, $saveAs)
  {
    try {
      $this->s3Client->getObject(array(
        'Bucket' => Configure::read('AWS.commits-bucket-name'),
        'Key'    => $awsKey,
        'SaveAs' => $saveAs
      ));
      return true;
    } catch (Exception $e) {
      App::import('Model', 'Message');
      Log::registerException($e, "Archive.php", "getCommitFileSavedFromAwsS3");
      return false;
    }
  }

  public function copyOutputFileToAwsS3($commitId, $meta = array())
  {
    try {
      $head = $this->s3Client->HeadObject(array(
        'Bucket' => Configure::read('AWS.outputfiles-bucket-name'),
        'Key' => $commitId . ".zip"
      ));
      return -1;
    } catch (Exception $e) {
      $localFile = Configure::read('Upload.dir') . DS . 'outputfiles' . DS . $commitId . '.zip';
      if (is_file($localFile)) {
        try {
          $key = $commitId . ".zip";
          $result = $this->s3Client->putObject(array(
            'Bucket' => Configure::read('AWS.outputfiles-bucket-name'),
            'Key' => $key,
            'SourceFile' => $localFile,
            'ACL' => 'private',
            'StorageClass' => 'REDUCED_REDUNDANCY',
            'Metadata' => $meta
          ));
          return true;
        } catch (Exception $e) {
          App::import('Model', 'Message');
          Log::register("Archive.php[method: copyCommitFiletoAwsS3]" . $e->getMessage());
          return false;
        }
      }
    }
  }

  public function getOutputFileFromAwsS3($commitId)
  {
    try {
      $head = $this->s3Client->HeadObject(array(
        'Bucket' => Configure::read('AWS.outputfiles-bucket-name'),
        'Key' => $commitId . ".zip"
      ));
      $cachedFile = $this->_cacheFile(Configure::read('AWS.outputfiles-bucket-name'), $commitId . ".zip", $head->get('LastModified')->__toString());
      if ($cachedFile) {
        return $cachedFile;
      } else {
        return Configure::read('Upload.dir') . DS . 'outputfiles' . DS . $commitId . '.zip';
      }
    } catch (Exception $e) {
      App::import('Model', 'Message');
      Log::registerException($e, "Archive.php", "getOutputFileFromAwsS3");
      return Configure::read('Upload.dir') . DS . 'outputfiles' . DS . $commitId . '.zip';
    }
  }

  public function copyFileToAwsS3($prefix, $file, $localFile, $meta = array())
  {
    try {
      $this->s3Client->HeadObject(array(
        'Bucket' => Configure::read('AWS.files-bucket-name'),
        'Key' => $prefix . DS . $file,
      ));
      return -1;
    } catch (Exception $e) {
      if (is_file($localFile)) {
        try {
          $key = $prefix . DS . $file;
          $result = $this->s3Client->putObject(array(
            'Bucket' => Configure::read('AWS.files-bucket-name'),
            'Key' => $key,
            'SourceFile' => $localFile,
            'ACL' => 'private',
            'StorageClass' => 'STANDARD',
            'Metadata' => $meta
          ));
          return true;
        } catch (Exception $e) {
          App::import('Model', 'Message');
          Log::register("Archive.php[method: copyFileToAwsS3]" . $e->getMessage());
          return false;
        }
      }
    }
  }

  public function copyDownloadableFileToAwsS3($key, $buffer, $fileName)
  {
    try {
      $result = $this->s3Client->putObject(array(
        'Bucket' => Configure::read('AWS.download-bucket-name'),
        'Key' => $key,
        'SourceFile' => $buffer,
        'ACL' => 'authenticated-read',
        'StorageClass' => 'STANDARD',
      ));
      $this->s3Client->waitUntil('ObjectExists', array(
        'Bucket' => Configure::read('AWS.download-bucket-name'),
        'Key'    => $key
      ));

      $command = $this->s3Client->getCommand('GetObject', array(
        'Bucket' => Configure::read('AWS.download-bucket-name'),
        'Key' => $key,
        'ContentType' => 'application/octet-stream',
        'ResponseContentDisposition' => 'attachment; filename="' . $fileName . '"'
      ));
      return $this->toPublicUrl((string) $this->s3Client->createPresignedRequest($command, '+10 minutes')->getUri());
    } catch (Exception $e) {
      App::import('Model', 'Message');
      Log::register("Archive.php[method: copyFileToAwsS3]" . $e->getMessage());
      return false;
    }
  }

  public function copyExerciseCaseFileToAwsS3($exerciseCaseId, $file, $localFile, $meta = array())
  {
    try {
      $key = $exerciseCaseId . DS . "files" . DS . $file;
      $this->s3Client->HeadObject(array(
        'Bucket' => Configure::read('AWS.cases-bucket-name'),
        'Key' => $key,
      ));
      return -1;
    } catch (Exception $e) {
      if (is_file($localFile)) {
        try {
          $result = $this->s3Client->putObject(array(
            'Bucket' => Configure::read('AWS.cases-bucket-name'),
            'Key' => $key,
            'SourceFile' => $localFile,
            'ACL' => 'private',
            'StorageClass' => 'STANDARD',
            'Metadata' => $meta
          ));
          return true;
        } catch (Exception $e) {
          App::import('Model', 'Message');
          Log::register("Archive.php[method: copyExerciseCaseFileToAwsS3]" . $e->getMessage());
          return false;
        }
      }
    }
  }

  public function getFileFromAwsS3($prefix, $file)
  {
    $split = explode(".", $file);
    $ext = $split[count($split) - 1];
    try {
      $result = $this->s3Client->getObject(array(
        'Bucket' => Configure::read('AWS.files-bucket-name'),
        'Key'    => $prefix . DS . $file
      ));
      return array("ext" => $ext, "result" => $result);
    } catch (Exception $e) {
      App::import('Model', 'Message');
      Log::registerException($e, "Archive.php", "getFileFromAwsS3");
      return false;
    }
  }

  public function getFileDownloadLinkFromAwsS3($prefix, $file)
  {
    try {

      $cmd = $this->s3Client->getCommand('GetObject', array(
        'Bucket' => Configure::read('AWS.files-bucket-name'),
        'Key'    => $prefix . DS . $file
      ));
      return $this->toPublicUrl($this->s3Client->createPresignedRequest($cmd, '+2 minutes')->getUri());
    } catch (Exception $e) {
      App::import('Model', 'Message');
      Log::registerException($e, "Archive.php", "getFileDownloadLinkFromAwsS3");
      return false;
    }
  }

  public function getExerciseCaseInputMD5FromAwsS3($exerciseCaseId)
  {
    $metadata = $this->getAwsFileMetadata($exerciseCaseId . "/in", Configure::read('AWS.cases-bucket-name'));
    return isset($metadata["md5"]) ? $metadata["md5"] : false;
  }

  public function getExerciseCaseOutputMD5FromAwsS3($exerciseCaseId)
  {
    $metadata = $this->getAwsFileMetadata($exerciseCaseId . "/out", Configure::read('AWS.cases-bucket-name'));
    return isset($metadata["md5"]) ? $metadata["md5"] : false;
  }

  public function getExerciseCaseInputFromAwsS3($exerciseCaseId)
  {
    return $this->getAwsFileContent($exerciseCaseId . "/in", Configure::read('AWS.cases-bucket-name'));
  }

  public function getExerciseCaseOutputFromAwsS3($exerciseCaseId)
  {
    return $this->getAwsFileContent($exerciseCaseId . "/out", Configure::read('AWS.cases-bucket-name'));
  }

  public function saveExerciseCaseInputToAwsS3($exerciseCaseId, $body, $metadata = array())
  {
    if (is_null($body)) $body = "";
    return $this->uploadContentToAWS($body, Configure::read('AWS.cases-bucket-name'), $exerciseCaseId, "in", $metadata);
  }

  public function saveExerciseCaseOutputToAwsS3($exerciseCaseId, $body, $metadata = array())
  {
    if (is_null($body)) $body = "";
    return $this->uploadContentToAWS($body, Configure::read('AWS.cases-bucket-name'), $exerciseCaseId, "out", $metadata);
  }

  public function copyExerciseCaseInputToAwsS3($exerciseCaseId, $tmpFileKey)
  {
    return $this->copyFilesInAWS(Configure::read('AWS.files-bucket-name'), $tmpFileKey, Configure::read('AWS.cases-bucket-name'), $exerciseCaseId . "/in");
  }

  public function copyExerciseCaseOutputToAwsS3($exerciseCaseId, $tmpFileKey)
  {
    return $this->copyFilesInAWS(Configure::read('AWS.files-bucket-name'), $tmpFileKey, Configure::read('AWS.cases-bucket-name'), $exerciseCaseId . "/out");
  }

  public function copyExerciseCaseFilesToAwsS3($exerciseCaseId, $tmpFileKey, $realFileName)
  {
    return $this->copyFilesInAWS(Configure::read('AWS.files-bucket-name'), $tmpFileKey, Configure::read('AWS.cases-bucket-name'), $exerciseCaseId . "/files/" . $realFileName);
  }

  public function copyExerciseCaseFileFromExerciseCaseFile($sourceExerciseCaseId, $targetExerciseCaseId, $realFileName)
  {
    return $this->copyFilesInAWS(Configure::read('AWS.cases-bucket-name'), $sourceExerciseCaseId . "/files/" . $realFileName, Configure::read('AWS.cases-bucket-name'), $targetExerciseCaseId . "/files/" . $realFileName);
  }

  public function deleteExerciseCaseInputFromAwsS3($exerciseCaseId)
  {
    return $this->_deleteFileFromAWS(Configure::read('AWS.cases-bucket-name'), $exerciseCaseId, "in");
  }

  public function deleteExerciseCaseOutputFromAwsS3($exerciseCaseId)
  {
    return $this->_deleteFileFromAWS(Configure::read('AWS.cases-bucket-name'), $exerciseCaseId, "out");
  }

  public function deleteExerciseCaseFileFromAwsS3($exerciseCaseId, $realFileName)
  {
    return $this->_deleteFileFromAWS(Configure::read('AWS.cases-bucket-name'), $exerciseCaseId, "files/" . $realFileName);
  }

  protected function _cacheFile($bucket, $awsKey, $lastmodified)
  {
    $split = explode(".", $awsKey);
    $ext = $split[count($split) - 1];
    $cacheDir = CACHE . "runcodesfiles" . DS;
    $cachedFile = $cacheDir . md5($awsKey . $lastmodified) . "." . $ext;
    $dir = new Folder($cacheDir, true, 0777);
    if (!is_file($cachedFile)) {
      try {
        $result = $this->s3Client->GetObject(array(
          'Bucket' => $bucket,
          'Key'    => $awsKey,
          'SaveAs' => $cachedFile
        ));
      } catch (Exception $e) {
        App::import('Model', 'Message');
        $Message = new Message();
        Log::registerException($e, "Archive.php", "_cacheFile");
        $Message->sendAlertEmail($e->getMessage(), "Error on caching commit " . $awsKey . " from AWS S3");
        return false;
      }
    }
    return $cachedFile;
  }

  public function uploadContentToAWS($content, $bucket, $prefix, $name, $metadata = array())
  {
    try {
      $result = $this->s3Client->putObject(array(
        'Bucket' => $bucket,
        'Key'    => $prefix . DS . $name,
        'Body' => $content,
        'Metadata' => $metadata
      ));
      return $prefix . DS . $name;
    } catch (Exception $e) {
      App::import('Model', 'Message');
      $Message = new Message();
      $Message->sendAlertEmail($e->getMessage(), "Error on upload file [" . $bucket . "] " . $prefix . DS . $name . "  to AWS S3");
      Log::registerException($e, "Archive.php", "uploadFileToAWS");
      return false;
    }
  }

  public function uploadFileToAWS($file, $bucket, $prefix, $name, $metadata = array())
  {
    try {
      $result = $this->s3Client->putObject(array(
        'Bucket' => $bucket,
        'Key'    => $prefix . DS . $name,
        'SourceFile' => $file,
        'Metadata' => $metadata
      ));
      return $prefix . DS . $name;
    } catch (Exception $e) {
      App::import('Model', 'Message');
      $Message = new Message();
      $Message->sendAlertEmail($e->getMessage(), "Error on upload file [" . $bucket . "] " . $prefix . DS . $name . "  to AWS S3");
      Log::registerException($e, "Archive.php", "uploadFileToAWS");
      return false;
    }
  }

  public function copyFilesInAWS($sourceBucket, $sourceKey, $targetBucket, $targetKey)
  {
    // Refactored to test an hypothesis of error on copyObject due to incompatibilities in the SeaweedFS //


    // Original source and target buckets parameters
    $sourceBucket = is_null($sourceBucket) ? Configure::read('AWS.files-bucket-name') : $sourceBucket;
    $targetBucket = is_null($targetBucket) ? Configure::read('AWS.files-bucket-name') : $targetBucket;

    try {
      // Download file to temporary location
      $tmpFilePath = tempnam(sys_get_temp_dir(), 's3_temp');
      $this->s3Client->getObject([
        'Bucket' => $sourceBucket,
        'Key'    => $sourceKey,
        'SaveAs' => $tmpFilePath
      ]);

      // Upload downloaded file to target bucket
      $result = $this->s3Client->putObject([
        'Bucket' => $targetBucket,
        'Key'    => $targetKey,
        'SourceFile' => $tmpFilePath
      ]);

      // Cleanup
      unlink($tmpFilePath);

      // Return target key (whis is kinda weird, but ok)
      return $targetKey;
    } catch (S3Exception $e) {
      CakeLog::error("S3Exception on copyFilesInAWS: " . $e);
    } catch (Exception $e) {
      CakeLog::error("Exception on copyFilesInAWS: " . $e);
      Log::registerException($e, "Archive.php", "uploadFileToAWS");
    }

    return false;
  }

  public function copyAwsFiles($tmpKey, $awsKey)
  {
    //Utilizado quando ambos arquivos ficam em files-bucket-name
    $prefix = "s3://" . Configure::read('AWS.files-bucket-name');
    $tmpKey = $prefix . $tmpKey;
    $awsKey = $prefix . $awsKey;
    try {
      if (is_file($tmpKey)) {
        return copy($tmpKey, $awsKey);
      }
      return false;
    } catch (Exception $e) {
      App::import('Model', 'Message');
      Log::registerException($e, "Archive.php", "copyAwsFiles");
      return false;
    }
  }

  public function getAwsFileMetadata($key, $bucket = null)
  {
    $bucket = is_null($bucket) ? Configure::read('AWS.files-bucket-name') : $bucket;
    try {
      $result = $this->s3Client->headObject(array(
        'Bucket' => $bucket,
        'Key'    => $key,
      ));
      $result = $result->toArray();
      return $result['Metadata'];
    } catch (Exception $e) {
      Log::registerException($e, "Archive.php", "getAwsFileMetadata");
      return false;
    }
  }

  public function getAwsFileContent($key, $bucket = null)
  {
    $bucket = is_null($bucket) ? Configure::read('AWS.files-bucket-name') : $bucket;
    $prefix = "s3://" . $bucket;
    $key = $prefix . DS . $key;
    $content = file_get_contents($key);
    if (!$content) {
      return '';
    }
    return $content;
  }

  //    public function handleCommitFileUpload ($upload,$prefix,$allowed_types = null,$max_size_limit = 2000000) {
  //        return $this->handleFileUpload($upload,$prefix,$allowed_types,$max_size_limit,Configure::read('AWS.commits-bucket-name'));
  //    }


  // This is not the ideal solution, but it is not worth to change the whole
  // behaviour of the commits upload for now. (Will be rewritten soon).
  private function extractExtension($fileName) {
      $split = explode(".", $fileName);

      if (count($split) == 1) {
        return $split[0];
      }

      $ext = $split[count($split) - 1];

      // Handle OpenMP and MPI for C/C++
      if (count($split) >= 3 && in_array(strtolower($ext), array("c", "cpp", "cc"))) {

        // If it is, check if the next extension is either omp or mpi
        if (in_array(strtolower($split[count($split) - 2]), array("omp", "mpi"))) {
          // Keep the mpi/omp prefix
          $ext = $split[count($split) - 2] . "." . $ext;
        }
      }

      return $ext;
  }

  public function handleCommitFileUpload($upload, $exerciseId, $commitId, $meta = array(), $allowed_types = null, $max_size_limit = 2000000)
  {
    $bucket = Configure::read('AWS.commits-bucket-name');

    //        Log::register("Commit: ".$commitId." ".var_export($upload,true));
    $result = array();
    $hash_time = sha1(time());
    if ($upload && is_array($upload['tmp_name'])) {
      //            Log::register("Multiple Commit Upload");
      $index = 0;

      $ext = $this->extractExtension($upload['name'][$index]);

      $result[$index] = new stdClass();
      $result[$index]->name = $commitId . "-" . $hash_time . "." . $ext; //$upload['name'][$index];
      $result[$index]->realname = $upload['name'][$index];
      $result[$index]->size = $upload['size'][$index];
      $result[$index]->hash_time = $hash_time;
      $result[$index]->type = $upload['type'][$index];
      if ($upload['error'][$index] != 0) {
        $result[$index]->error = __("Error on upload");
        //                Log::register("Error on upload file (Error code: " . $upload['error'][$index] . ")");
      } else {
        if (is_array($allowed_types)) {
          if (!in_array(strtolower($ext), $allowed_types)) {
            $result[$index]->error = __("The uploaded file have a filetype not allowed to this exercise");
            //                        Log::register("Error on upload file (Filetype not allowed)");
          }
        }
        if ($upload['size'][$index] > $max_size_limit) {
          $max_kb = $max_size_limit / 1000;
          $result[$index]->error = __("File size bigger than maximum size allowed (%s Kb)", $max_kb);
          //                    Log::register("Error on upload file (Max upload file size)");
        }
        //                Log::register("CF: ".var_export($result,true));
        $result[$index]->aws = (isset($result[0]->error)) ? false : $this->uploadFileToAWS($upload['tmp_name'][$index], $bucket, $exerciseId, $result[$index]->name);
        if ($result[$index]->aws === false) {
          $result[$index]->error = (isset($result[0]->error)) ? $result[0]->error : __("Error on upload");
        }
      }
    } else {
      $result[0]->error = __("Error on upload");
      return array("files" => $result);
    }
    return array("files" => $result);
  }


  public function handleFileUpload($upload, $prefix, $allowed_types = null, $max_size_limit = 6000000, $bucket = null)
  {
    if (is_null($bucket)) $bucket = Configure::read('AWS.files-bucket-name');
    //        Log::register("CF: ".var_export($upload,true));
    $result = array();
    $hash_time = sha1(time());
    if ($upload && is_array($upload['tmp_name'])) {
      foreach ($upload['tmp_name'] as $index => $value) {
        $split = explode(".", $upload['name'][$index]);
        $ext = $split[(count($split) - 1) < 0 ? 0 : count($split) - 1];
        $result[$index] = new stdClass();
        $result[$index]->name = $upload['name'][$index];
        $result[$index]->realname = $upload['name'][$index];
        $result[$index]->size = $upload['size'][$index];
        $result[$index]->hash_time = $hash_time;
        $result[$index]->type = $upload['type'][$index];
        if ($upload['error'][$index] != 0) {
          $result[$index]->error = __("Error on upload");
          //                    Log::register("Error on upload file (Error code: " . $upload['error'][$index] . ")");
        } else {
          if (is_array($allowed_types)) {
            if (!in_array($ext, $allowed_types)) {
              $result[$index]->error = __("Filetype not allowed");
              //                            Log::register("Error on upload file (Filetype not allowed)");
            }
          }
          if ($upload['size'][$index] > $max_size_limit) {
            $max_kb = $max_size_limit / 1000;
            $result[$index]->error = __("File size bigger than maximum size allowed (%s Kb)", $max_kb);
            //                        Log::register("Error on upload file (Max upload file size)");
          }
          //                    Log::register("CF: ".var_export($result,true));

          $metadata = array("md5" => md5(file_get_contents($upload['tmp_name'][$index])));

          if (!$this->uploadFileToAWS($upload['tmp_name'][$index], $bucket, $prefix . DS . $hash_time, $upload['name'][$index], $metadata)) {
            $result[$index]->error = __("Error on upload");
          }
        }
      }
    } else {
      $index = 0;
      $split = explode(".", $upload['name']);
      $ext = $split[(count($split) - 1) < 0 ? 0 : count($split) - 1];
      $result[$index] = new stdClass();
      $result[$index]->name = $upload['name'];
      $result[$index]->realname = $upload['name'];
      $result[$index]->size = $upload['size'];
      $result[$index]->hash_time = $hash_time;
      $result[$index]->type = $upload['type'];
      if ($upload['error'] != 0) {
        $result[0]->error = __("Error on upload");
        //                Log::register("Error on upload file (Error code: " . $upload['error'] . ")");
      } else {
        if (is_array($allowed_types)) {
          if (!in_array($ext, $allowed_types)) {
            $result[$index]->error = __("Filetype not allowed");
            //                        Log::register("Error on upload file (Filetype not allowed)");
          }
        }
        if ($upload['size'] > $max_size_limit) {
          $max_kb = $max_size_limit / 1000;
          $result[$index]->error = __("File size bigger than maximum size allowed (%s Kb)", $max_kb);
          //                    Log::register("Error on upload file (Max upload file size)");
        }
        if (!$this->uploadFileToAWS($upload['tmp_name'], $bucket, $prefix . DS . $hash_time, $upload['name'])) {
          $result[$index]->error = __("Error on upload");
        }
      }
    }
    return array("files" => $result);
  }



  private function _deleteFileFromAWS($bucket, $prefix, $name)
  {
    try {
      $result = $this->s3Client->deleteObject(array(
        'Bucket' => $bucket,
        'Key'    => $prefix . DS . $name
      ));
      return true;
    } catch (Exception $e) {
      App::import('Model', 'Message');
      $Message = new Message();
      $Message->sendAlertEmail($e->getMessage(), "Error on deleteFileFromAWS [" . $bucket . "] " . $prefix . DS . $name);
      Log::registerException($e, "Archive.php", "deleteFileFromAWS");
      return false;
    }
  }

  private function toPublicUrl($url)
  {
    $loader = new ConfigLoader();
    return str_replace($loader->configs["RUNCODES_S3_ENDPOINT"], $loader->configs["RUNCODES_S3_PUBLIC_ENDPOINT"], $url);
  }
}
