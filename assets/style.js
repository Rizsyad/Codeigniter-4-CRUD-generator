function reset() {
    localStorage.clear();
}

function checkLocalStorage() {
    if (("path" in localStorage) ||
        ("host" in localStorage) ||
        ("username" in localStorage) ||
        ("password" in localStorage) ||
        ("database" in localStorage)) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This project is already saved, do you want to create a new one?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, create new!'
        }).then((result) => {
            if (result.isConfirmed) {
                reset();
                $("#setup").show();
            } else {
                $("#builder").show();
                getTable();
            }
        })
    } else {
        reset();
        $("#setup").show();
    }
}

function getTable() {
    $.ajax({
        url: 'core/core.php',
        type: 'POST',
        data: {
            'action': 'getTable',
            'host': localStorage.getItem('host'),
            'username': localStorage.getItem('username'),
            'password': localStorage.getItem('password'),
            'database': localStorage.getItem('database')
        },
        success: (data) => {
            $("#selectable").append(data);
        }
    })
}

function setPath() {
    $("#setpath").on("click", () => {
        var path = $("#path").val();
        $.ajax({
            url: 'core/core.php',
            type: 'POST',
            dataType: 'JSON',
            data: {
                'action': 'getConnection',
                'path': path
            },
            success: async function (data) {
                if(data.success === false) {
                    Swal.fire("Error",data.message,"error");
                    return;
                }
                
                var {
                    host,
                    username,
                    password,
                    database
                } = data;

                var arr = ['host', 'username', 'password', 'database'];

                await arr.forEach(element => {
                    $(`#${element}`).val(eval(element));
                });

                $("#setdb").show();
                localStorage.setItem('path', path);
                localStorage.setItem('host', host);
                localStorage.setItem('username', username);
                localStorage.setItem('password', password);
                localStorage.setItem('database', database);
            }
        });
    });
}

function checkPermission() {
    $.ajax({
        url: 'core/core.php',
        type: 'POST',
        dataType: 'JSON',
        data: {
            'action': 'checkPermission',
            'path': localStorage.getItem("path")
        },
        success: (data) => {
            if (data.success === false) {
                Swal.fire('Error', data.message, 'error');
                return;
            }

            Swal.fire({
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
            })

            getTable();
        }
    });
}

async function DescriptionTable(table) {
    window.table = table;

    $("#selectedColumns").show();
    await $.ajax({
        url: 'core/core.php',
        type: 'post',
        data: {
            'action': 'getDescriptionTable',
            'host': localStorage.getItem('host'),
            'username': localStorage.getItem('username'),
            'password': localStorage.getItem('password'),
            'database': localStorage.getItem('database'),
            'table': table
        },
        success: (data) => {
            $("#setfield").html(data);
        }
    });
}