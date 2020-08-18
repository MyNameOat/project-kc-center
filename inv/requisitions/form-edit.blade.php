<?php
    use Crabon\Carbor;
?>
@extends('layouts-inv.app')
@section('content')

<div class="row">
    <div class="row wrapper wrapper bg-white animated fadeInRight">
        <div class="ibox-title" style="height:120px;">
            <div class="col-lg-6">
                <span><h2>ใบเบิกสินค้า</h2>
                <span>หน้าหลัก/ใบเบิกทั้งหมด</span>
            </div>
            <div class="col-lg-6"><br>
                <span class="pull-right">

                </span>
            </div>
        </div>
    </div>
</div>
<div class="row wrapper wrapper bg-white animated fadeInRight">
    <div class="ibox" style="margin-top: 30px;">
        <form action="{{ route('inv.update-store') }}" method="POST">
            @csrf
            @foreach ($requisitions as $requisition)
                <div class="ibox-content col-lg-12">
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-lg-4">
                            <span>
                                <h2>ใบเบิกสินค้า เลขที่ ({{ $requisition->code }})</h2>
                                <input type="text" name="requisition_id" value="{{ $requisition->id }}">
                            </span>
                        </div>
                        <div class="col-lg-4">
                            <label>วันที่เอกสาร ::
                                {{ $document_at  = date("d/m/Y") }}
                                <input type="hidden" name="document_at" value="{{ $document_at  }}">
                            </label>
                        </div>
                        <div class="col-lg-4">
                            <label>คลังเก็บสินค้า :: {{ $requisition->warehouse->name }}
                                {{ $warehouse_id = session('warehouse')['id'] }}
                                <input type="hidden" name="edit_warehouse_id" value="{{ $requisition->warehouse->id }}">
                            </label>
                            ผู้บันทึก ::{{ $requisition->createUser->name }}
                            <input type="text" name="edit_user_id" value="{{ $requisition->createUser->id }}" readonly>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="ibox wrapper wrapper bg-white animated fadeInRight">
                                    {{-- Table Good Append  --}}
                                    @include('inv.table-good-append')
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12" style="margin-top: 30px;">
                            <span>
                                <h4>ประเภทการเบิก / หมายเหตุ</h4>
                            </span><br>
                            <label>ประเภทการเบิก <font color="red">*</font></label><br>

                            <select class="form-control" name="edit_take_id" id="take_id" value="{{ $requisition->take->id }}">
                                    <option value="{{ $requisition->take->id }}">-- {{ $requisition->take->name }} --</option>
                                @foreach ($takes as $take)
                                    <option value="{{ $take->id }}">{{ $take->name }}</option>
                                @endforeach
                            </select><br>

                            <label>หมายเหตุ</label><br>
                            <textarea style="width: 100%" name="edit_detail" id="edit_detail" cols="122" rows="6"
                                value="{{ $requisition->detail }}">{{ $requisition->detail }}
                            </textarea>

                            <span class="pull-right">
                                {{-- <button type="button" onclick="onSubmitRequisition()" id="buttonSubmit" class="btn btn-primary">บันทึก</button> --}}
                                <button type="submit" class="btn btn-primary waves-effect">บันทึก</button>
                                <button class="btn btn-danger waves-effect ajaxify" type="button"
                                onclick="location.href='{{ route('inv.approve-detail', $requisition->id) }}'">ยกเลิก{{$requisition->id}}</button>
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </form>
    </div>
</div>

{{-- Table Show Good Select Append --}}
@include('inv.extra-good')

@endsection
@section('script')
<script type="text/javascript">

    var type_id = "all";
        var warehouse_id = $('#warehouse_id').val();
        var countModal = true;
        var tableSearch;
        function getGoodModal() {
            if(countModal){
                    tableSearch = $('#tableGood').DataTable({
                        pageLength: 15,
                        autoWidth: true,
                        searching: true,
                        "bInfo": false,
                        ordering: true,
                        "bLengthChange": false,
                        responsive: true,
                        processing: true,
                        ajax: {
                            url: "{{ route('inv.search-goods') }}",
                            type: "POST",
                            data: function(d) {
                                d._token = "{{ csrf_token() }}";
                                d.type_id = type_id;
                            },
                        },
                        columns: [
                            { "data": "code"},
                            { "data": "name"},
                            { "data": "amount"},
                            { "data": "unit"},
                            { "data": "check"},
                        ],
                    });
                }else{
                    reloadTableSearch();
                }
            $('#goodModal').modal('show');
    }

    function onSubmitRequisition(button) {
		$('#buttonSubmit').prop( "disabled", true );
        var take = document.getElementById('take_id').value;
		if (take == '') {
			alert('กรุณาเลือกประเภทการเบิก');
			$('#buttonSubmit').prop( "disabled", false );
		}else{
			$('#form').submit();
		}
	}

    $(document).on("click", ".bt-type-search", function () {
        type_id = $(this).data('type_id');
        reloadTableSearch();
    });

    function reloadTableSearch() {
        tableSearch.ajax.reload();
    }

    function showAlert(message) {
        return swal({
            title: "ขออภัย",
            text: message,
            type: 'warning',
        });
    }

    $('#check-sku-all').on('click', function () {
        var rows = $(this).closest('table');
        $('td input:checkbox', rows).prop('checked', this.checked);
    });

    $('#selectProduct').on('click', function () {
        tr = $('#tableGood').find('tbody').find('tr');
        tr.each(function () {
            if ($(this).find('.check-list').prop('checked')) {
                var good_id = $(this).find('.good_id').val();
                var coil_code = $(this).find('.coil_code').val();
                var code = $(this).find('td').eq(0).text();
                var warehouse_good_id = $(this).find('.warehouse_good_id').val();
                var name = $(this).find('td').eq(1).text();
                var amount = $(this).find('td').eq(2).text();
                var unit = $(this).find('td').eq(3).text();
                // var unit = $(this).find('.unit_name').val();

                addTable(good_id,coil_code,code,name,amount,unit,warehouse_good_id);
            }
        });
        $('input[type=checkbox]').prop('checked', false);
        $('#goodModal').modal('hide');
    });

    function addTable(good_id,coil_code,code,name,amount,unit,warehouse_good_id) {
        name = decodeURIComponent(name).replace(/\+/g, ' ');

        var addRow = $(' .addTr ').clone(true);
            addRow.removeClass(' addTr ');
            addRow.find(' .warehouse_good_id ').val(warehouse_good_id);
            addRow.find(' .good_code ').text(code);
            addRow.find(' .coil_code ').val(coil_code);
            addRow.find(' .good_id ').val(good_id);
            addRow.find(' .amount ').val(0);
            addRow.find(' .good_name ').text(name);
            addRow.find(' .unit_name ').text(unit);
            addRow.find(' .amount_in_warehouse ').text(amount);
            $('#goodAppend').append(addRow);
    }

     function deleteRow(r) {
        var row = r.parentNode.parentNode;
        row.remove();
    }


	amount_limit = [];
	ratio_cost = [];

	function displayNumbers() {
		var numbers = 5; // replace 9 with your desire number
		var r = '';
		for (var i = 0; i < numbers; i++) {
			r += parseInt((Math.random() * 100), 10);
		}
		return r;
	}

	function formatNumber(number) {
		var number = number.toFixed(2) + '';
		var x = number.split('.');
		var x1 = x[0];
		var x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/;
		while (rgx.test(x1)) {
			x1 = x1.replace(rgx, '$1' + ',' + '$2');
		}
		return x1 + x2;
	}

	function addRow() {

		var row = displayNumbers();
		var num = $('tr[data-attr=row]').length;
		num++;
		var html = '<tr data-attr="row" data-id="' + row +
			'"> <td class="col-lg-2"> <div class="input-group"> <div class="fg-line"> <input type="text" readonly class="form-control" id="code_' +
			row + '" /> <input type="hidden" class="form-control" name="good_id[]" id="id_' + row +
			'" /> </div> <span class="input-group-addon last" id="icon_' + row + '" onclick="getGoodModal(' + row +
			')"  style="cursor: pointer"> <i class="fa fa-search"></i> </span> </div> </td> <td class="col-lg-2"> <div class="fg-line coil-select_' +
			row + '"> <input type="hidden" name="coil_code[]" id="coil_id_' + row +
			'" /> <input type="text" readonly class="form-control" id="coil_code_' + row +
			'" /> </div> </td> <td> <label id="name_' + row +
			'"></label> </td> <td class="col-lg-1"> <div class="fg-line"> <input type="text" class="form-control" name="amount[]" id="goods_number_' +
			row + '" value="0.00" onkeyup="totalCost(' + row +
			')" /> </div> </td> <td class="col-lg-1"> <input type="hidden" name="unit_id[]" id="unit_' + row +
			'"> <div class="fg-line"> <input type="text" class="form-control" id="good_unit_' + row +
			'" readonly /> </div> </td> <td class="col-lg-1"> <div class="fg-line"> <label id="ratio_cost_' + row +
			'"></label> </div> </td> <td class="col-lg-1"> <div class="fg-line"> <input type="text" data-type="number_decimal" class="form-control" name="cost[]" id="cost_' +
			row +
			'" readonly /> </div> </td> <td class="col-lg-2"> <div class="fg-line"> <button type="button" name="button" class="btn btn-info" onClick="showModal(' +
			row + ')">ดูต้นทุน</button> <button type="button" name="button" class="btn btn-danger"onClick="deleteGoods(' +
			row + ')">ลบ</button> </div> </td> </tr>';
		$('#goodAppend').append(html);
		$('input[data-type=number]').number(true, 0);
	}

	function deleteGoods(row) {

		var amount = $('#goods_amount_row_' + row + '').val();

		var result_final = $('#goods_amount_real_final').val();

		var result_final_2 = parseInt(result_final) - parseInt(amount);

		var result_sum_format = formatNumber(parseInt(result_final) - parseInt(amount));

		for (i = 1; i < 4; i++) {
			$('#goods_amount_all_' + i + '').val(result_sum_format);
		}

		$('#goods_amount_all_final').val(result_sum_format);

		$('tr[data-id=' + row + ']').remove();

		$('#goods_amount_real_final').val(result_final_2)

	}

	function totalCost(row) {
		var amount = +$('#goods_number_' + row).val();
		var sum = 0;
		var i = 0;

		for (i = 0; i < amount_limit[row].length; i++) {
			if (amount <= amount_limit[row][i]) {
				sum = amount * ratio_cost[row][i];
				amount = 0;
				break;
			} else {
				sum = amount_limit[row][i] * ratio_cost[row][i];
				amount = amount - amount_limit[row][i];
			}
		}

		if (amount == 0) {
			$('#cost_' + row).val(sum.toFixed(2));
		} else {
			alert('คุณกรอกจำนวนเกินจำนวนสูงสุด');
			$('#goods_number_' + row).val(0.00);
		}
	}

	function showModal(row) {
		var rows = "";
		$("#tbodyratio").empty();
		var amount = +$('#goods_number_' + row).val();
		var i = 0;
		var sum = +$('#cost_' + row).val();
		if (amount <= amount_limit[row][i]) {
			rows += "<tr><td>" + amount + "</td>";
			rows += "<td>" + +ratio_cost[row, i] + "</td>";
			rows += "<td>" + (amount * ratio_cost[row, i]).toFixed(2) + "</td></tr>";
		} else {
			rows += "<tr><td>" + amount_limit[row][i] + "</td>";
			rows += "<td>" + ratio_cost[row, i] + "</td>";
			rows += "<td>" + (amount_limit[row][i] * ratio_cost[row, i]).toFixed(2) + "</td></tr>";
			amount = amount - amount_limit[row][i];
			i = i + 1;
			rows = addModal(rows, amount, i);
		}

		rows += "<tr><td colspan='2' style='font-weight:bold;'>รวม</td><td>" + sum + "</td></tr>";
		$(rows).appendTo("#showRatio tbody");
		$('#ratioModal').modal('show');
	}

	function addModal(rows, amount, i) {
		if (!!amount_limit[row][i]) {
			if (amount <= amount_limit[row][i]) {
				rows += "<tr><td>" + amount + "</td>";
				rows += "<td>" + ratio_cost[row, i] + "</td>";
				rows += "<td>" + (amount * ratio_cost[row, i]).toFixed(2) + "</td></tr>";
				return rows;
			} else {
				rows += "<tr><td>" + amount_limit[row][i] + "</td>";
				rows += "<td>" + ratio_cost[row, i] + "</td>";
				rows += "<td>" + (amount_limit[row][i] * ratio_cost[row, i]).toFixed(2) + "</td></tr>";
				amount = amount - amount_limit[row][i];
				i = i + 1;
				rows = addModal(rows, amount, i);
				return rows;
			}
		} else {
			alert('คุณกรอกจำนวนเกินจำนวนสูงสุด');
			$('#goods_number_' + row).val('0.00');
		}
	}

</script>
@endsection
