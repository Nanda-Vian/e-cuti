CURRENT_PATH = ADMIN_PATH + "/karyawan/";

tableId = "#listUser";

moveRoom(tableId);

function setStatus(status, id) {
    confirmSweet("Anda yakin ingin merubah status ?").then((result) => {
        if (isConfirmed(result)) {
            $.ajax({
                url: CURRENT_PATH + "set/" + id,
                data: {
                    _token: TOKEN,
                    status: status,
                },
                type: "POST",
                dataType: "JSON",
                beforeSend: function () {
                    disableButton();
                },
                complete: function () {
                    enableButton();
                },
                dataSrc: function (json) {
                    json?.status == "401" &&
                        msgSweetWarning("Sesi Anda berakhir !").then((msg) => {
                            doLogoutAjax();
                        });
                    return json.data;
                },
                success: function (result) {
                    "ok" == result.status
                        ? (refreshData(),
                          enableButton(),
                          toastSuccess(result.message))
                        : (enableButton(), toastError(result.message, "Gagal"));
                },
                error: function (error) {
                    errorCode(error);
                },
            });
        }
    });
}

function refreshData() {
    table.ajax.reload(null, !1);
}
$(document).ready(function () {
    $("#statusField").attr("style", "width:70px");
    $("#actionField").attr("style", "width:115px; text-align:center");
    table = $(tableId).DataTable({
        processing: !0,
        serverSide: !0,
        order: [],
        ajax: {
            url: API_PATH + "data/users",
            type: "POST",
            data: {
                _token: TOKEN,
            },
            complete: function () {
                checkPilihan({
                    table: tableId,
                    buttons: ["reset", "delete", "active", "deactive"],
                    path: CURRENT_PATH,
                });
            },
            dataSrc: function (json) {
                json?.status == "401" &&
                    msgSweetWarning("Sesi Anda berakhir !").then((msg) => {
                        doLogoutAjax();
                    });
                json?.status == "fail" && toastError(json?.message, "Gagal");
                return json.data;
            },
            error: function (error) {
                errorCode(error);
            },
        },
        fnCreatedRow: function (nRow, aData, iDataIndex) {
            $(nRow).attr("data-id", aData.id);
        },
        columns: dataColumnTable([
            "id",
            "nrk",
            "nama",
            "jabatan",
            "unit_kerja",
            "level",
            "active",
        ]),
        columnDefs: [
            {
                targets: [0],
                orderable: !1,
                sClass: "text-center",
                render: function (data, type, row) {
                    return (
                        "<input type='checkbox' id='checkItem-" +
                        row.id +
                        "' value='" +
                        row.id +
                        "'>"
                    );
                },
            },
            {
                sClass: "text-left",
                targets: [1],
                orderable: !0,
                render: function (data, type, row) {
                    return `${row.nrk} / ${row.nip}`;
                },
            },
            {
                sClass: "text-center",
                targets: [5],
                orderable: !0,
                render: function (data, type, row) {
                    let level = ["User", "Penyetuju", "Admin"];
                    return level[row.level] ?? "-";
                },
            },
            {
                sClass: "text-center",
                targets: [6],
                orderable: !0,
                render: function (data, type, row) {
                    return 1 == data
                        ? "<button class='btn btn-success btn-sm' id='on' data-id=" +
                              row.id +
                              " data-toggle='tooltip' title='User Aktif'><i class='fas fa-toggle-on'></i> On</button>"
                        : "<button class='btn btn-danger btn-sm' id='off' data-id=" +
                              row.id +
                              " data-toggle='tooltip' title='User Tidak Aktif'><i class='fas fa-toggle-off'></i> Off</button>";
                },
            },
            {
                sClass: "text-center",
                targets: [7],
                orderable: !0,
                render: function (data, type, row) {
                    return (
                        "<button class='btn btn-danger btn-sm' id='delete' data-id=" +
                        row.id +
                        " data-toggle='tooltip' title='Hapus Data'><i class='fas fa-trash-alt'></i></button> \n <button class='btn btn-warning btn-sm' id='edit' data-id=" +
                        row.id +
                        " data-toggle='tooltip' title='Edit Data'><i class='fas fa-pencil-alt'></i></button> \n <button class='btn btn-info btn-sm' id='reset' data-id=" +
                        row.id +
                        " data-toggle='tooltip' title='Reset Password'><i class='fas fa-sync-alt'></i></button>"
                    );
                },
            },
        ],
    });
}),
    $(tableId).delegate("#delete", "click", function () {
        confirmSweet("Anda yakin ingin menghapus data ?").then((result) => {
            if (isConfirmed(result)) {
                let id = $(this).data("id");
                result &&
                    $.ajax({
                        url: CURRENT_PATH + "delete",
                        data: {
                            _token: TOKEN,
                            id: id,
                        },
                        type: "POST",
                        dataType: "JSON",
                        beforeSend: function () {
                            disableButton();
                        },
                        success: function (result) {
                            "ok" == result.status
                                ? (enableButton(),
                                  toastSuccess(result.message),
                                  refreshData())
                                : toastError(result.message, "Gagal");
                        },
                        error: function (error) {
                            errorCode(error);
                        },
                    });
            }
        });
    }),
    $(tableId).delegate("#edit", "click", function () {
        let id = $(this).data("id");
        $.ajax({
            url: API_PATH + "row/users/" + id,
            type: "post",
            data: { _token: TOKEN },
            dataType: "json",
            beforeSend: function () {
                disableButton();
                clearFormInput("#formBody");
                addFormInput("#formBody", [
                    {
                        type: "hidden",
                        name: "id",
                    },
                    {
                        type: "email",
                        name: "email",
                        label: "Email",
                    },
                    {
                        type: "text",
                        name: "nama",
                        label: "Nama",
                    },
                    {
                        type: "number",
                        name: "nip",
                        label: "NIP",
                    },
                    {
                        type: "number",
                        name: "nrk",
                        label: "NRK",
                    },
                    {
                        type: "select2",
                        name: "unit_kerja_id",
                        label: "Unit Kerja",
                        api: {
                            url: API_PATH + "data/options/unit-kerja",
                            option: {
                                value: "id",
                                caption: "{nama}",
                            },
                            order: {
                                nama: "asc",
                            },
                        },
                    },
                    {
                        type: "select2",
                        name: "jabatan_id",
                        label: "Jabatan",
                        api: {
                            url: API_PATH + "data/options/jabatan",
                            option: {
                                value: "id",
                                caption: "{nama}",
                            },
                            order: {
                                nama: "asc",
                            },
                        },
                    },
                    {
                        type: "select2",
                        name: "level",
                        label: "Level",
                        data: {
                            0: "User",
                            1: "Penyetuju",
                            2: "Admin",
                        },
                    },
                    {
                        type: "date",
                        name: "tahun_masuk",
                        label: "Tahun Masuk",
                    },
                    {
                        type: "number",
                        name: "cuti_tahun_jatah",
                        label: `Jatah Cuti Tahun ${TAHUN}`,
                    },
                    {
                        type: "number",
                        name: "sisa_tahun_min1",
                        label: `Sisa Tahun ${TAHUN - 1}`,
                    },
                    {
                        type: "number",
                        name: "sisa_tahun_min2",
                        label: `Sisa Tahun ${TAHUN - 2}`,
                    },
                    // {
                    //     type: "select2",
                    //     name: "approval_1",
                    //     label: "Approval 1",
                    //     api: {
                    //         url: API_PATH + "data/options/users",
                    //         option: {
                    //             value: "id",
                    //             caption: "{nip} - {nama}",
                    //         },
                    //         where: {
                    //             level: 1,
                    //         },
                    //         order: {
                    //             nama: "asc",
                    //         },
                    //     },
                    // },
                    // {
                    //     type: "select2",
                    //     name: "approval_2",
                    //     label: "Approval 2",
                    //     api: {
                    //         url: API_PATH + "data/options/users",
                    //         option: {
                    //             value: "id",
                    //             caption: "{nip} - {nama}",
                    //         },
                    //         where: {
                    //             level: 1,
                    //         },
                    //         order: {
                    //             nama: "asc",
                    //         },
                    //     },
                    // },
                    // {
                    //     type: "select2",
                    //     name: "approval_3",
                    //     label: "Approval 3",
                    //     api: {
                    //         url: API_PATH + "data/options/users",
                    //         option: {
                    //             value: "id",
                    //             caption: "{nip} - {nama}",
                    //         },
                    //         where: {
                    //             level: 1,
                    //         },
                    //         order: {
                    //             nama: "asc",
                    //         },
                    //     },
                    // },
                ]);
            },
            complete: function () {
                enableButton();
            },
            success: function (result) {
                "ok" == result.status
                    ? ($("#modalForm").modal("show"),
                      $("#modalTitle").html("Edit Pengguna"),
                      $("#formInput").attr("action", CURRENT_PATH + "update"),
                      fillForm(result.data))
                    : msgSweetError(result.message);
            },
            error: function (err) {
                errorCode(err);
            },
        });
    }),
    $(tableId).delegate("#reset", "click", function (e) {
        confirmSweet("Anda yakin ingin mereset password ?").then((result) => {
            if (isConfirmed(result)) {
                let id = $(this).data("id");
                result &&
                    $.ajax({
                        url: CURRENT_PATH + "reset/" + id,
                        data: {
                            _token: TOKEN,
                        },
                        type: "POST",
                        dataType: "JSON",
                        beforeSend: function () {
                            disableButton();
                        },
                        complete: function () {
                            enableButton();
                        },
                        success: function (result) {
                            "ok" == result.status
                                ? toastSuccess(result.message)
                                : toastError(result.message, "Gagal");
                        },
                        error: function (error) {
                            errorCode(error);
                        },
                    });
            }
        });
    }),
    $(tableId).delegate("#on", "click", function () {
        setStatus("off", $(this).data("id"));
    }),
    $(tableId).delegate("#off", "click", function () {
        setStatus("on", $(this).data("id"));
    }),
    $("#btnAdd").on("click", function () {
        clearFormInput("#formBody");
        addFormInput("#formBody", [
            {
                type: "email",
                name: "email",
                label: "Email",
            },
            {
                type: "text",
                name: "nama",
                label: "Nama",
            },
            {
                type: "number",
                name: "nip",
                label: "NIP",
            },
            {
                type: "number",
                name: "nrk",
                label: "NRK",
            },
            {
                type: "select2",
                name: "unit_kerja_id",
                label: "Unit Kerja",
                api: {
                    url: API_PATH + "data/options/unit-kerja",
                    option: {
                        value: "id",
                        caption: "{nama}",
                    },
                    order: {
                        nama: "asc",
                    },
                },
            },
            {
                type: "select2",
                name: "jabatan_id",
                label: "Jabatan",
                api: {
                    url: API_PATH + "data/options/jabatan",
                    option: {
                        value: "id",
                        caption: "{nama}",
                    },
                    order: {
                        nama: "asc",
                    },
                },
            },
            {
                type: "select2",
                name: "level",
                label: "Level",
                data: {
                    0: "User",
                    1: "Penyetuju",
                    2: "Admin",
                },
            },
            {
                type: "date",
                name: "tahun_masuk",
                label: "Tahun Masuk",
            },
            {
                type: "number",
                name: "cuti_tahun_jatah",
                label: `Jatah Cuti Tahun ${TAHUN}`,
            },
            // {
            //     type: "number",
            //     name: "sisa_tahun_min1",
            //     label: `Sisa Tahun ${TAHUN - 1}`,
            // },
            // {
            //     type: "number",
            //     name: "sisa_tahun_min2",
            //     label: `Sisa Tahun ${TAHUN - 2}`,
            // },
        ]);
        $("#modalForm").modal("show");
        $("#modalTitle").html("Tambah Karyawan");
        $("#formInput").attr("action", CURRENT_PATH + "store");
    }),
    $("#formInput").submit(function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: $(this).attr("action"),
            type: "post",
            data: formData,
            processData: !1,
            contentType: !1,
            cache: !1,
            dataType: "JSON",
            beforeSend: function () {
                disableButton();
            },
            complete: function () {
                enableButton();
            },
            success: function (e) {
                validate(e.validate.input),
                    e.validate.success &&
                        ("ok" == e.status
                            ? (toastSuccess(e.message),
                              refreshData(),
                              1 == e.modalClose &&
                                  $("#modalForm").modal("hide"),
                              clearInput(e.validate.input))
                            : toastWarning(e.message));
            },
            error: function (err) {
                errorCode(err);
            },
        });
    });
