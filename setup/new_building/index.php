<?php
/**
 *------------------------------------------------------------------------------
 *  Building Information Setup Index Page
 *------------------------------------------------------------------------------
 *
 *
**/




?>

<script type="text/javascript">
function validate(){
	var input = [	[document.forms["new_building"]["name"].value,		"name"],
					[document.forms["new_building"]["address1"].value,	"address1"],
					[document.forms["new_building"]["city"].value,		"city"],
					[document.forms["new_building"]["state"].value,		"state"]
				];
	var errorMsg = "Invalid entries";
	var error = false;
	for(var i=0;i<input.length;i++){
		document.forms["new_building"][input[i][1]].style.border = "";
		if((input[i][0] == null) || (input[i][0] == "")){
			document.forms["new_building"][input[i][1]].style.border = "red solid 1px";
			error = true;
		}
	}

	var zip = document.forms["new_building"]["zip"].value;
	document.forms["new_building"]["zip"].style.border = "";
	if((isNaN(zip)) || (zip == null) || (zip == "") || (zip.length != 5)){
		document.forms["new_building"]["zip"].style.border = "red solid 1px";
		error = true;
	}

	if(error){
		alert(errorMsg);
		return false;
	}
}
</script>

<form name="new_building" onsubmit="return validate()">
    <div class="row">
    	<span style="color:red">*</span> Required Fields<br><br>
        <div class="span12">
            <label for="name"><span style="color:red">*</span> Building Name
               <input type="text" class="span12" name="name">
            </label>
            <label for="address1"><span style="color:red">*</span> Address<br>
                <input type="text" class="span6" name="address1">
            </label>
            <label for="address2">Address Line 2<br>
                <input type="text" class="span6" name="address2">
            </label>
            <label for="city"><span style="color:red">*</span> City<br>
                <input type="text" class="span3" name="city">
            </label>
            <label for="state"><span style="color:red">*</span> State<br>
            	<select name="state" class="span3"><?php
					foreach($state_list as $abbr => $state) {?>
						<option value="<?=$abbr?>"><?=$state?></option>
					<?php
					}
					?>
                </select>
            </label>
            <label for="zip"><span style="color:red">*</span> Zip<br>
                <input type="text" class="span3" name="zip">
            </label>
        </div>
    </div>
    <div class="row">
        <div class="span10 offset1">
            <button type="submit" class="btn btn-success">
                <i class="icon-ok icon-white"></i>
                Submit
            </button>
            <a href="../" class="btn pull-right">
                <i class="icon-remove"></i>
                Cancel
            </a>
        </div>
    </div>
</form>