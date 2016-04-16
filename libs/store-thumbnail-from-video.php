<?php

class StoreThumbnailFromVideo
{
	public static $instance = null;
	public $nonce = '';
	public $name = 'store-thumbnail-from-video';
	private $ffmpeg;
	private $pluginPath;
	private $ffmpegPath;
	private $ffmpegBin;
	
	public static function getInstance()
	{
		null === self::$instance AND self::$instance = new self;
		return self::$instance;
	}
	
	public function __construct()
	{
		$this->pluginPath = ABSPATH . 'wp-content/plugins/custom-video-actions/';
		include $this->pluginPath . 'vendor/autoload.php';
		$this->ffmpegPath = $this->pluginPath . 'bin/ffmpeg-3.0.1-64bit-static/';
		//$this->ffmpegBin = $this->ffmpegPath . 'ffmpeg';
		$this->ffmpegBin = 'ffmpeg';
		add_action('save_post', array($this, 'storeThumbnailFromVideo'));
		add_action('save_post', array($this, 'storePreviewFromVideo'));
	}
	
	public function getCompleteVideoPath($post_id)
	{
		if(get_post_type($post_id) != 'download')
		{
			return false;
		}
		if(!empty($_POST['files']))
		{
			$videoUrls = $_POST['files'];
			$videoUrl = $videoUrls[0];
				
		}
		else if(!empty($_POST['edd_download_files']))
		{
			$videoElements = $_POST['edd_download_files'];
			$videoElement = $videoElements[0];
			$videoUrl = $videoElement['file'];
		}
		else
		{
			return false;
		}
		$videoPath = explode('/wp-content/uploads', $videoUrl);
		$pathPart = $videoPath[1];
		$completeVideoPath = wp_upload_dir()['basedir'].$pathPart;
		return $completeVideoPath;
	}
	
	public function storeThumbnailFromVideo($post_id)
	{
		$completeVideoPath = $this->getCompleteVideoPath($post_id);
		if($completeVideoPath == false)
		{
			return false;
		}
		$todayDate = new DateTime();
		$year = $todayDate->format("Y");
		$month = $todayDate->format("m");
		$featUploadPath = wp_upload_dir()['basedir'].'/'.$year.'/'.$month;
		if (!file_exists($featUploadPath)) {
			mkdir($featUploadPath, 0777, true);
		}
		else 
		{
			chmod ($featUploadPath, 0777);
		}
		$featFilePath = $featUploadPath.'/feature-image-'.time().'.jpg';
		$cmd = "'".$this->ffmpegBin."' -i '".$completeVideoPath."' -ss 00:00:10 -vframes 1 '".$featFilePath."'";
		shell_exec($cmd);
		$filetype = wp_check_filetype(basename($featFilePath), null);
		$attachment = array(
			'guid'           => wp_upload_dir()['url'] . '/' . basename($featFilePath),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace('/\.[^.]+$/', '', basename($featFilePath)),
			'post_content'   => '',
			'post_status'    => 'publish'
		);
		$attachId = wp_insert_attachment( $attachment, $featFilePath);
		update_post_meta($post_id, '_thumbnail_id', $attachId);
	}
	
	public function storePreviewFromVideo($post_id)
	{
		$completeVideoPath = $this->getCompleteVideoPath($post_id);
		if($completeVideoPath == false)
		{
			return false;
		}
		$dirPath = pathinfo($completeVideoPath, PATHINFO_DIRNAME);
		$filename = pathinfo($completeVideoPath, PATHINFO_FILENAME);
		$extension = pathinfo($completeVideoPath, PATHINFO_EXTENSION);
		$newFilePath = $dirPath.'/'.$filename.'-preview.'.$extension;
		$cmd = "'".$this->ffmpegBin."' -i '".$completeVideoPath."' -ss 00:00:00 -t 00:00:20 -async 1 '".$newFilePath."'";
		shell_exec($cmd);
		$filetype = wp_check_filetype(basename($newFilePath), null);
		$attachment = array(
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace('/\.[^.]+$/', '', basename($newFilePath)),
				'post_content'   => '',
				'post_status'    => 'publish'
		);
		$attachId = wp_insert_attachment( $attachment, $newFilePath);
		update_post_meta($post_id, '_preview_id', $attachId);
	}
}