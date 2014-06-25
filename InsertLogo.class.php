class InsertLogo//水印类
  {
  
    private $source;//主图片路径
	private $logo;//水印图片路径
	private $source_type;//主图片类型
	private $logo_type;//水印图片类型
	private $source_width;//主图片宽度
	private $source_height;//主图片高度
	private $logo_width;//水印图片宽度
	private $logo_height;//水印图片高度
	private $tinyImage_width;//略缩图宽
	private $tinyImage_height;//略缩图高
	private $newPicPath;//生成水印图片地址
	private $tinyImagePath;//生成略缩图存放路径
	function __construct($source,$logo)//传入图片路径
	{
	  $this->source=$source;
	  $info=GetImageSize($source);
	  $this->source_width=$info[0];
	  $this->source_height=$info[1];
	  $this->source_type_id=$info[2];
	  $this->source_type=$info['mime'];//其值 1 为 GIF 格式、 2 为 JPEG/JPG 格式、3 为 PNG 格式
	  $this->logo=$logo;
	  $info=GetImageSize($logo);
	  $this->logo_width=$info[0];
	  $this->logo_height=$info[1];
	  $this->logo_type_id=$info[2];
	  $thi->logo_type=$logo['mime'];
	}
	//1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP info[2]图片类型
	function JudgeTypeAndDeal($type,$source)//判断并处理,返回PHP可识别编码
    {
	  if($type==1)//能处理的三种图片。可在上传类限制图片。
	  {
	    return ImageCreateFromGIF($source);	
	  }
	  else if($type==2)
	  {
	    return ImageCreateFromJPEG($source);
	  }
	  else
	  {
	    return ImageCreateFromPNG($source);
	  }
	}
	function CreateLogoImage()//生成水印图
	{
	  $this->source=$this->JudgeTypeAndDeal($this->source_type_id,$this->source);//取得主图片编码
	  $this->logo=$this->JudgeTypeAndDeal($this->logo_type_id,$this->logo);//取得水印编码
	  $x=$this->source_width-$this->logo_width;
	  $y=$this->source_height-$this->logo_height;
	  $w=$this->logo_width;
	  $h=$this->logo_height;
	  /* echo "x=".$x;
	  echo ",y=".$y;
	  echo ",w=".$w;
	  echo ",h=".$h; */
	  ImageCopy($this->source,$this->logo,$x,$y,0,0,$w,$h)or die("fail to combine");
	  $this->newPicPath='App/Upload/image/normal/1108000627/new.jpg';
	  ImageJpeg($this->source,'new.jpg');
      rename('new.jpg',$this->newPicPath);//放到相应文件位置
    } 	 
	function CreateTinyImage()//生成略缩图，生成略缩图原则要控制图片比例，防止比例不协调，可改参数，尝试显示一部分原比例部分图片
	{
	  $TinyImage=imagecreatetruecolor($this->source_width*0.3,$this->source_height*0.3);
	  $this->source=$this->JudgeTypeAndDeal($this->source_type_id,$this->source);//取得主图片编码
	  ImageCopyResized($TinyImage,$this->source,0,0,0,0,$this->source_width*0.3,$this->source_height*0.3,$this->source_width,$this->source_height) or die("fail");
	  $this->tinyImagePath='App/Upload/image/tiny/1108000627/new.jpg';
	  ImageJpeg($TinyImage,'new.jpg');
      rename('new.jpg',$this->tinyImagePath);//放到相应文件位置
	}
  }
  /*添加水印类生成略缩图类END*/
