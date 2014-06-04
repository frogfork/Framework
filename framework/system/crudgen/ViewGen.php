<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace system\crudgen;

/**
 * Description of ControllerGen
 *
 * @author masfu
 */
use system\core\Base;

class ViewGen extends Generator {

    public function __construct() {
        parent::__construct();
    }

    public function make() {
        $field = "";
        foreach ($this->attributes as $key => $value) {

            $col = Base::instance()->input->post('col_' . $value);

            if ($col != "") {
                $label = Base::instance()->input->post('label_' . $value);
                $typeform = Base::instance()->input->post('typeform_' . $value);
                $required = Base::instance()->input->post('required_' . $value) ? 'required' : '';
                $min = Base::instance()->input->post('min_' . $value);
                $max = Base::instance()->input->post('max_' . $value);
                $field .= "<div class=\"form-group\">
    <label for=\"$col\" class=\"col-sm-2 control-label\">$label</label>
    <div class=\"col-sm-10\">
      <input type=\"$typeform\" class=\"form-control\" $required id=\"$col\" placeholder=\"$label\">
    </div>
  </div>";
            }
        }
        $filename = __DIR__ . '/layout/view/template.tpl';
        $template = file_get_contents($filename);
        $template = str_replace('{field}', $field, $template);

        return $template;
    }

    public function run() {
        $action = Base::instance()->input->post('action');
        switch ($action) {
            case 'write_file':
                $code = $this->make();
                $dir = VIEWS_PATH . $this->table;
                if (!is_dir($dir)) {
                    mkdir($dir, 0777);
                }
                $filename = $dir . '/form' . EXT_FILE;
                $this->view->code = $code;
                $this->write($filename, $code);
                break;
            case 'download' :
                $code = $this->make();
                $dir = VIEWS_PATH . $this->table;
                if (!is_dir($dir)) {
                    mkdir($dir, 0777);
                }
                $filename = $dir . '/form' . EXT_FILE;
                $this->download($filename, $code);
                exit();
            default:
                break;
        }
        $db = Base::instance()->db->getConnection();
        $this->view->columns = $db->columns($this->table);
        echo $this->view->display('view/index.php');
    }

}
