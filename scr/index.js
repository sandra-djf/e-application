$(document).ready(function () {
    var table = $('#tableD').DataTable();
    $("option").click(function() {
  		table.column(3)
                    .search( this.value )
                    .draw();
});
});

