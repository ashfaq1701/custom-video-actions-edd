<?php

class ConvertVideo
{
	public static $instance = null;
	public $nonce = '';
	public $name = 'convert-video';
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
		add_action('add_attachment', array($this, 'convertVideo'));
	}
	
	public function convertVideo($attachment_ID)
	{
		$mimeType = get_post_mime_type($attachment_ID);
		if(is_bool(strpos($mimeType, 'video')) && strpos($mimeType, 'video') == false)
		{
			return false;
		}
		$filepath = get_attached_file($attachment_ID);
		$dirPath = pathinfo($filepath, PATHINFO_DIRNAME);
		$filename = pathinfo($filepath, PATHINFO_FILENAME);
		$extension = pathinfo($filepath, PATHINFO_EXTENSION);
		if($extension != 'mp4')
		{
			$newPath = $dirPath.'/'.$filename.'.mp4';
			$cmd = "'".$this->ffmpegBin."' -i '".$filepath."' -vcodec copy -acodec copy '".$newPath."'";
			shell_exec($cmd);
			update_attached_file($attachment_ID, $newPath);
			chmod($newPath, 0777);
			unlink($filepath);
		}
	}
}