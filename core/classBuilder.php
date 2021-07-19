<?php

class Builder
{

    public function Controller($dataController)
    {
        $namefile = ucfirst(preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($dataController["nmfile"])));
        $namefileModel = ucfirst(preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($dataController["nmfile"]))) . "Model";
        $namefileView = preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($dataController["nmfile"]));

        $templates = file_get_contents("../templates/Controller.txt");
        $templates = str_replace("{{NAMEFILE}}", $namefile, $templates);
        $templates = str_replace("{{NAMEFILEMODEL}}", $namefileModel, $templates);
        $templates = str_replace("{{NAMEFILEVIEW}}", $namefileView, $templates);

        $templates = str_replace("{{SELECTFIELDS}}", $dataController["primarykey"] . "," . str_replace($dataController["primarykey"] . ",", "", $dataController["select"]), $templates);
        $templates = str_replace("{{PRIMARYKEY}}", $dataController["primarykey"], $templates);
        $templates = str_replace("{{TITLE}}", ucwords($dataController["title"]), $templates);

        $fields_store = [];
        $rules_store = [];

        foreach (explode(",", str_replace($dataController["primarykey"] . ",", "", $dataController["select"])) as $value) {
            $namelabel =  ucwords(preg_replace('/[^A-Za-z0-9\-]/', ' ', strtolower($value)));
            $fields_store[$value] = '"' . $value . '" => htmlentities($this->request->getPost("' . $value . '"), ENT_QUOTES, "UTF-8"),';
            $rules_store[$value] = '"' . $value . '" => ["label" => "' . $namelabel . '", "rules" => "trim|required"],';
        }

        $templates = str_replace("{{FIELDSSTORE}}", implode($fields_store), $templates);
        $templates = str_replace("{{RULESSTORE}}", implode($rules_store), $templates);

        $fields_update = [];
        $rules_update = [];

        foreach (explode(",", $dataController["primarykey"] . "," . str_replace($dataController["primarykey"] . ",", "", $dataController["select"])) as $value) {
            $namelabel =  ucwords(preg_replace('/[^A-Za-z0-9\-]/', ' ', strtolower($value)));
            $fields_update[$value] = '"' . $value . '" => htmlentities($this->request->getPost("' . $value . '"), ENT_QUOTES, "UTF-8"),';
            $rules_update[$value] = '"' . $value . '" => ["label" => "' . $namelabel . '", "rules" => "trim|required"],';
        }

        $templates = str_replace("{{FIELDSUPDATE}}", implode($fields_update), $templates);
        $templates = str_replace("{{RULESUPDATE}}", implode($rules_update), $templates);

        return $templates;
    }

    public function Model($dataModel)
    {
        $namefile = ucfirst(preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($dataModel["nmfile"]))) . "Model";

        $templates = file_get_contents("../templates/Model.txt");
        $templates = str_replace("{{NAMEFILE}}", $namefile, $templates);
        $templates = str_replace("{{TABLE}}", $dataModel["table"], $templates);
        $templates = str_replace("{{PRIMARYKEY}}", $dataModel["primarykey"], $templates);
        $templates = str_replace("{{SELECTFIELDS}}", json_encode(explode(",", str_replace($dataModel["primarykey"] . ",", "", $dataModel["select"]))), $templates);

        return $templates;
    }

    public function View($dataView)
    {
        $templates = file_get_contents("../templates/View.txt");
        $array_buttons = [
            'copy',
            'csv',
            'pdf',
            'excel',
            'print',
        ];

        $form_add = "";
        $form_update = "";
        $dataTables = "";
        $columndefs = '{
            title: "Action",
            targets: ' . sizeof(explode(",", str_replace($dataView["primarykey"] . ",", "", $dataView["select"]))) . ',
            render: function(data, type, row, meta) {
                return \'\
                <button class="btn btn-warning text-white btn-sm" onclick="edit(\' + row.' . $dataView["primarykey"] . ' + \');"><i class="fas fa-edit"></i></button> | \
                <button class="btn btn-danger text-white btn-sm" onclick="remove(\' + row.' . $dataView["primarykey"] . ' + \');"><i class="fas fa-trash"></i></button>\';
            }
        }';
        $editjavascript = "";
        $datatablesbutton = "";


        foreach (explode(",", str_replace($dataView["primarykey"] . ",", "", $dataView["select"])) as $value) {
            $namelabel =  ucwords(preg_replace('/[^A-Za-z0-9\-]/', ' ', strtolower($value)));
            $form_add .= '
            <div class="form-group">
                <label for="' . $value . '">' . $namelabel . '</label>
                <input type="text" name="' . $value . '" id="' . $value . '_add" class="form-control" required />
            </div>';
            $dataTables .= '{
                title: "' . $namelabel . '",
                data: "' . $value . '",
                className: "export"
            },';
            $editjavascript .= '$("#' . $value . '").val(data.' . $value . ');';
        }

        foreach (explode(",", $dataView["primarykey"] . "," . str_replace($dataView["primarykey"] . ",", "", $dataView["select"])) as $value) {
            $namelabel =  ucwords(preg_replace('/[^A-Za-z0-9\-]/', ' ', strtolower($value)));
            if ($value == $dataView["primarykey"]) $form_update .= '<input type="hidden" name="' . $dataView["primarykey"] . '" id="' . $dataView["primarykey"] . '" />';
            else
                $form_update .= '
            <div class="form-group">
                <label for="' . $value . '">' . $namelabel . '</label>
                <input type="text" name="' . $value . '" id="' . $value . '" class="form-control" required />
            </div>';
        }

        foreach ($array_buttons as $button) {
            $datatablesbutton .= "
            {
                text: '<i class=\"fas fa-" . (($button == "pdf" || $button == "csv" || $button == "excel") ? "file-$button" : $button) . "\"></i> " . (($button == "pdf" || $button == "csv" || $button == "excel") ? ucwords("download $button") : ucfirst($button)) . "',
                extend: '$button',
                title: 'Export <?= ucwords(\$title); ?>',
                className: 'btn btn-outline-primary my-2',
                exportOptions: {
                    columns: '.export'
                }
            },";
        }

        $templates = str_replace("{{DATATABLESBUTTON}}", $datatablesbutton, $templates);
        $templates = str_replace("{{HTMLADD}}", $form_add, $templates);
        $templates = str_replace("{{HTMLUPDATE}}", $form_update, $templates);
        $templates = str_replace("{{DATATABLES}}", $dataTables, $templates);
        $templates = str_replace("{{COLUMNDEFS}}", $columndefs, $templates);
        $templates = str_replace("{{ARRAYSELECT}}", json_encode(explode(",", str_replace($dataView["primarykey"] . ",", "", $dataView["select"]))), $templates);
        $templates = str_replace("{{PRIMARYKEY}}", $dataView["primarykey"], $templates);
        $templates = str_replace("{{EDITJAVASCRIPT}}", $editjavascript, $templates);



        // {
        //     extend: 'copy',
        //     className: 'btn btn-outline-primary my-2'
        // },
        // {
        //     extend: 'csv',
        //     className: 'btn btn-outline-primary my-2'
        // },
        // {
        //     extend: 'pdf',
        //     title: 'Export Data Users',
        //     className: 'btn btn-outline-primary my-2'
        // },
        // {
        //     extend: 'excel',
        //     title: 'Export Data Users',
        //     className: 'btn btn-outline-primary my-2'
        // },
        // {
        //     extend: 'print',
        //     title: 'Export Data Users',
        //     className: 'btn btn-outline-primary my-2'
        // },

        return $templates;
    }
}

// $builder = new Builder();
