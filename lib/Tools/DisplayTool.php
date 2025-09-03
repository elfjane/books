<?php
namespace Lib\Tools;
use Smarty\Smarty;
class DisplayTool
{
    protected $template = null;
    private $path = null;
    protected $cls;
    public $directory = '';
	public function __construct()
	{
	}

    public function show($type, $displayData, $class, $instance)
    {
        switch ($type)
        {
            case 'smarty':
            case 'smarty2':
            {
                $this->cls = $instance->getView();
                $this->smarty($displayData);
            } break;
            case 'json':
            {
                $this->cls = $class;
                $this->json($displayData);
            } break;
        }
    }

    public function json($displayData)
    {
        header('Content-Type: application/json');
        header('X-Status: success');
        echo json_encode($displayData);
    }

    public function smarty($displayData)
    {
        $this->path = CRON_BASE_PATH.'/views';

        $this->template = new Smarty();
        $this->template->setTemplateDir ($this->path . '/' . 'templates');
        $this->template->setCompileDir($this->path . '/' . 'templates_c');
        $this->template->setCacheDir($this->path . '/' . 'cache');
        $this->template->setConfigDir($this->path . '/' . 'configs');
        $this->template->setLeftDelimiter("<{");
        $this->template->setRightDelimiter("}>");
        $this->template->setCaching(false);
        $this->template->setDebugging(false);

        foreach ($displayData as $row => $val)
        {
            $this->template->assign($row, $val);
        }
        $this->template->display($this->cls.".tpl.htm");
    }
}
