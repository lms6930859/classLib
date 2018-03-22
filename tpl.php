<?php
namespace Framework;
class Tpl
{
	//缓存文件路径
	protected $cacheDir = './cache';
	//模板文件路径
	protected $tplDir = './tpl';
	//生命有效期
	protected $lifeTime = 3600;
	//保存分配过来的变量  $this->assign();
	protected $vars = array();
	
	//初始化一批成员属性
	
	public function __construct($cacheDir = null , $tplDir = null , $lifeTime = null)
	{
		//判断缓存文件路径
		if (isset($cacheDir)) {
			if ($this->checkDir($cacheDir)) {
				$this->cacheDir = $cacheDir;
			}
		}
		//判断模板文件路径
		if (isset($tplDir)) {
			if ($this->checkDir($tplDir)) {
				$this->tplDir = $tplDir;
			}
		}
		//判断生命有效期
		
		if (isset($lifeTime)) {
			$this->lifeTime = $lifeTime;
		}
	}
	
	//检测模板还有缓存路径的方法
	
	protected function checkdir($path)
	{
		if (!file_exists($path) || !is_dir($path)) {
			return mkdir($path , 0777 , true);
		}
		
		if (!is_readable($path) || !is_writeable($path)) {
			return chmod($path , 0777);
		}
		
		return true;
	}
	
	//保存变量
	public function assign($name , $val)
	{
		$this->vars[$name] = $val;
	}
	
	//显示模板的方法
	public function display($filePath , $isExecute = true)
	{
		//检测模板文件是否为空
		if (empty($filePath)) {
			exit('模板文件没有传入');
		}
		//生成模板文件的路径
		$tplFilePath = rtrim($this->tplDir, '/') . '/' . $filePath;
		
		if (!file_exists($tplFilePath)) {
			exit('模板文件不存在');
		}
		
		//生成缓存文件路径
		$cacheFilePath = rtrim($this->cacheDir , '/') . '/' . $filePath . '.php';
		
		//判断缓存文件是否存在
		if (!file_exists($cacheFilePath)) {
			//exit('缓存文件路径不存在');
		
			//执行编译
			//$html = file_get_contents();
			$html = $this->complie($tplFilePath);
			
			if (!file_put_contents($cacheFilePath , $html)) {
				exit('文件写入失败');
			}
			
		}
		
		//判断生命周期
		//缓存文件创建时间 + 手动设置的时间 > time()  //过期 还是不过期
		$lifeTime = filectime($cacheFilePath) + $this->lifeTime > time() ? false : true;
		
		//判断模板文件的修改的时间是否大于缓存文件的修改时间
		
		$tplTime = filemtime($tplFilePath) > filemtime($cacheFilePath) ? true : false;
		
		if ($lifeTime || $tplTime) {
			unlink($cacheFilePath);
			//再次执行编译
			
			$html = $this->complie($tplFilePath);
			
			if (!file_put_contents($cacheFilePath , $html)) {
				exit('文件写入失败');
			}
		}
		
		if($isExecute) {
			extract($this->vars);
			include $cacheFilePath;
		}
		
		
	}
	
	//编译文件的方法
	
	protected function complie($tplFilePath)
	{
		$html = file_get_contents($tplFilePath);
		
		$key = [
				'{if %%}' => '<?php if(\1): ?>',
				'{else}' => '<?php else : ?>',
				'{else if %%}' => '<?php elseif(\1) : ?>',
				'{elseif %%}' => '<?php elseif(\1) : ?>',
				'{/if}' => '<?php endif;?>',
				'{$%%}' => '<?=$\1;?>',
				'{foreach %%}' => '<?php foreach(\1) :?>',
				'{/foreach}' => '<?php endforeach;?>',
				'{for %%}' => '<?php for(\1):?>',
				'{/for}' => '<?php endfor;?>',
				'{while %%}' => '<?php while(\1):?>',
				'{/while}' => '<?php endwhile;?>',
				'{continue}' => '<?php continue;?>',
				'{break}' => '<?php break;?>',
				'{$%% = $%%}' => '<?php $\1 = $\2;?>',
				'{$%%++}' => '<?php $\1++;?>',
				'{$%%--}' => '<?php $\1--;?>',
				'{comment}' => '<?php /* ',
				'{/comment}' => ' */ ?>',
				'{/*}' => '<?php /* ',
				'{*/}' => '* ?>',
				'{section}' => '<?php ',
				'{/section}' => '?>',
				'{{%%(%%)}}' => '<?=\1(\2);?>',
				'{include %%}' => '<?php include "\1";?>'
		];
		
		
		foreach ($key as $keys => $val) {
			$pattern = '#'.str_replace('%%' , '(.+)' , preg_quote($keys , '#')).'#imsU';
			
			$replace = $val;
			
			if (stripos($keys , 'include')) {
				//处理包含的问题
				$html = preg_replace_callback($pattern , array($this , 'parseInclude') , $html);
				
				
			} else {
				$html = preg_replace($pattern , $replace , $html);
			}
		}
		return $html;
		
	}
	
	protected function parseInclude($data)
	{
		//var_dump($data);
		$file = str_replace('\'' , '' , $data[1]);
		
		$path = $this->parsePath($file);
		
		$this->display($file , false);
		
		$string = '<?php include "'.$path.'";?>';
		
		return $string;
	}
	
	//处理包含路径
	protected function parsePath($file)
	{
		return rtrim($this->cacheDir , '/') . '/' . $file . '.php';
	}
	
	
}






















