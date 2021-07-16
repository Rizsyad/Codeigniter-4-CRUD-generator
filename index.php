<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeIgniter 4 Generator</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" />
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">CodeIgniter 4 Generator</a>
    </nav>
    <div class="container mt-5">
        <div class="card" id="setup" style="display: none;">
            <div class="card-header text-center bg-primary text-white h3 text-uppercase font-weigth-bold">Setup</div>
            <div class="card-body">
                <div class="form-group">
                    <label for="path">Path Project</label>
                    <input type="text" id="path" class="form-control" placeholder="path/project/codeigniter4/" value="/path/project/codeigniter4" />
                </div>
                <button id="setpath" class="btn btn-primary float-right">Set Path</button>
                <div id="setdb" class="mt-5" style="display: none;">
                    <div class="form-group">
                        <label for="host">Host</label>
                        <input type="text" id="host" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="text" id="password" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label for="database">Database</label>
                        <input type="text" id="database" class="form-control" />
                    </div>
                    <button id="next" class="btn btn-primary float-right text-capitalize">next</button>
                </div>

            </div>
        </div>
        <div class="card" id="builder" style="display: none;">
            <div class="card-header text-center bg-primary text-white h3 text-uppercase font-weigth-bold">Builder</div>
            <div class="card-body">
                <div class="form-group">
                    <label for="selectable">Select Table</label>
                    <select id="selectable" class="form-control">
                        <option value="">=== Select Table ===</option>
                    </select>
                </div>
                <div id="selectedColumns" class="mt-4" style="display: none;">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>
                                    <div class="d-flex justify-content-center">
                                        <input id="selectall" type="checkbox" class="mx-auto form-check-input position-static" />
                                    </div>
                                </th>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Null</th>
                                <th>Key</th>
                                <th>Default</th>
                                <th>Extra</th>
                            </tr>
                        </thead>
                        <tbody id="setfield">
                        </tbody>
                    </table>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="title">Title</label>
                            <input type="text" id="title" class="form-control text-capitalize" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="validationDefault02">Name File</label>
                            <input type="text" id="nmfile" class="form-control text-capitalize" required>
                        </div>
                    </div>
                    <button id="generate" class="btn btn-primary float-right">Generate</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/style.js"></script>
    <script>
        // var table = "";

        $(document).ready(() => {
            checkLocalStorage();
            setPath();
            $("#next").on("click", async () => {
                await $.ajax({
                    url: 'core/core.php',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        'action': 'checkConnection',
                        'host': $("#host").val(),
                        'username': $("#username").val(),
                        'password': $("#password").val(),
                        'database': $("#database").val()
                    },
                    success: (data) => {
                        if (data.success === false) {
                            Swal.fire('Error', data.message, 'error');
                            return;
                        }

                        Swal.fire('Success', data.message, 'success').then(() => {
                            checkPermission();
                            $("#setup").hide();
                            $("#builder").show();
                        });
                    }
                });
            });

            $("#selectable").on("change", function() {
                if ($(this).val() === '') return $("#selectedColumns").hide();
                DescriptionTable($(this).val());
                $("#title").val('data ' + $(this).val());
                $("#nmfile").val($(this).val());
            });

            $("#selectall").change(function() {
                if (this.checked) {
                    $(".checksingle").each(function() {
                        this.checked = true;
                    })
                } else {
                    $(".checksingle").each(function() {
                        this.checked = false;
                    })
                }
            });

            $("body").on("click", ".checksingle", function() {
                if ($(this).is(":checked")) {
                    var isAllChecked = 0;
                    $(".checksingle").each(function() {
                        if (!this.checked)
                            isAllChecked = 1;
                    })
                    if (isAllChecked == 0) {
                        $("#selectall").prop("checked", true);
                    }
                } else {
                    $("#selectall").prop("checked", false);
                }
            });

            $("#generate").on('click', function() {

                $.ajax({
                    url: 'core/core.php',
                    type: 'post',
                    data: {
                        'action': 'getPrimaryKey',
                        'host': localStorage.getItem('host'),
                        'username': localStorage.getItem('username'),
                        'password': localStorage.getItem('password'),
                        'database': localStorage.getItem('database'),
                        'table': window.table
                    },
                    success: function(data) {

                        var checkedSelect = $('.checksingle:checkbox:checked').map(function() {
                            return this.value;
                        });

                        localStorage.setItem('select', Object.values(checkedSelect).slice(0, -2).join(','));
                        localStorage.setItem('primarykey', data);
                        localStorage.setItem('title', $("#title").val());
                        localStorage.setItem('nmfile', $("#nmfile").val());
                        localStorage.setItem('table', window.table);

                        Swal.fire({
                            title: 'Everything is done',
                            text: "Do you want to make this CRUD?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, build it!'
                        }).then((result) => {
                            if (result.isConfirmed) {

                                $.ajax({
                                    url: 'core/core.php',
                                    type: 'post',
                                    dataType: 'JSON',
                                    data: {
                                        'action': 'build',
                                        'data': {
                                            "nmfile": localStorage.getItem('nmfile'),
                                            "select": localStorage.getItem('select'),
                                            "primarykey": localStorage.getItem('primarykey'),
                                            "title": localStorage.getItem('title'),
                                            "table": localStorage.getItem('table'),
                                            "path": localStorage.getItem('path'),
                                        }
                                    },
                                    success: function(data) {
                                        if (data.success === false) {
                                            Swal.fire('Error', data.message, 'error');
                                            return;
                                        }
                                        Swal.fire('Success', data.message, 'success');
                                    }
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>