<?php
namespace Lib\Tools;
class DisplayTool
{
    private $template = null;
    private $path = null;
    protected $cls;
    public $directory = '';
	public function __construct()
	{
	}

    public function show($type, $displayData, $class)
    {
        switch ($type)
        {
            case 'smarty2':
            case 'json':
            {
                $this->cls = $class;
                $this->$type($displayData);
            } break;
        }
    }

    public function json($displayData)
    {
        header('Content-Type: application/json');
        header('X-Status: success');
        echo json_encode($displayData);
    }
}
