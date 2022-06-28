CURRENT_PATH = ADMIN_PATH + "/unit-kerja/";
tableId = "#unitKerja";
moveRoom(tableId);

function refreshData() {
    table.ajax.reload(null, !1);
}
$(document).ready(function () {
    $(tableId).attr("style", "width:115px; text-align:center;");
    table = $(tableId).DataTable({
        processing: !0,
        serverSide: !0,
        order: [],
        ajax: {
            url: API_PATH + "data/unit-kerja",
            type: "POST",
            data: {
                _token: TOKEN,
            },
            complete: function (response) {
                checkPilihan({
                    table: tableId,
                    buttons: ["delete"],
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
        columns: dataColumnTable(["id", "nama"]),
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
                targets: [1],
                orderable: 1,
                sClass: "text-left",
                render: function (data, type, row) {
                    return row.nama;
                },
            },
            {
                sClass: "text-center",
                targets: [2],
                orderable: !0,
                render: function (data, type, row) {
                    return (
                        "<button class='btn btn-danger btn-sm' id='delete' data-id=" +
                        row.id +
                        " title='Hapus Data'><i class='fas fa-trash-alt'></i></button> \n <button class='btn btn-warning btn-sm' id='edit' data-id=" +
                        row.id +
                        " title='Edit Data'><i class='fas fa-pencil-alt'></i></button>"
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
                                  refreshData(),
                                  socket.emit?.("affectDataTable", tableId))
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
            url: API_PATH + "row/unit-kerja/" + id,
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
                        type: "text",
                        name: "nama",
                        label: "Unit Kerja",
                    },
                ]);
            },
            complete: function () {
                enableButton();
            },
            success: function (result) {
                "ok" == result.status
                    ? ($("#modalForm").modal("show"),
                      $("#modalTitle").html("Edit Unit Kerja"),
                      $("#formInput").attr("action", CURRENT_PATH + "update"),
                      fillForm(result.data))
                    : msgSweetError(result.message);
            },
            error: function (err) {
                errorCode(err);
            },
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
                type: "text",
                name: "nama",
                label: "Unit Kerja",
            },
        ]);
        $("#modalTitle").html("Tambah Unit Kerja");
        $("#formInput").attr("action", CURRENT_PATH + "store");
        $("#modalForm").modal("show");
    }),
    $("#formInput").submit(function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        formData.append("_token", TOKEN);
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
                              clearInput(e.validate.input),
                              socket.emit?.("affectDataTable", tableId))
                            : toastWarning(e.message));
            },
            error: function (err) {
                errorCode(err);
            },
        });
    });
