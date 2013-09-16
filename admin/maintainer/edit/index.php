<?php
/**
 *------------------------------------------------------------------------------
 * Add a New Maintenance Provider
 *------------------------------------------------------------------------------
 */
require_once('../../../includes/pageStart.php');

if($_SESSION['authLevel'] < 3) {
    gtfo($config);
}
if(!isset($_GET['id'])) {
    header('Location: ../../?a=e');
}

$db = new db($config);

if(count($_POST)) {
    $query = "
        UPDATE MaintainResource
        SET
            Category    = :Category,
            Name        = :Name,
            Company     = :Company,
            Address1    = :Address1,
            Address2    = :Address2,
            City        = :City,
            State       = :State,
            Zip         = :Zip,
            Phone       = :Phone,
            PhoneMobile = :PhoneMobile,
            Speciality    = :Speciality
        WHERE Recnum = :id
        LIMIT 1
    ";
    $bind = array(
        ':Category'     => $_POST['Category'],
        ':Name'         => $_POST['Name'],
        ':Company'      => $_POST['Company'],
        ':Address1'     => $_POST['Address1'],
        ':Address2'     => $_POST['Address2'],
        ':City'         => $_POST['City'],
        ':State'        => $_POST['State'],
        ':Zip'          => $_POST['Zip'],
        ':Phone'        => $_POST['Phone'],
        ':PhoneMobile'  => $_POST['PhoneMobile'],
        ':Speciality'   => $_POST['Speciality'],
        ':id'           => $_GET['id']
    );

    $resp = $db->execute($query, $bind);
    if($resp == true) {
        header('Location: ../../?a=s');
    }else{
        header('Location: ../../?a=e');
    }
}

$query = "SELECT * FROM MaintainResource WHERE Recnum = :id";
$bind = array(':id' => intval($_GET['id']));
$resource = $db->fetchRow($query, $bind);

require_once('../../../includes/header.php');
?>

        <div class="row">
            <h1 class="span8 offset2">New Maintenance Provider</h1>
        </div>

        <form method="POST">
        <div class="row">
            <div class="span5">
                <label class="row">
                    <span class="span1 align-right">Name</span>
                    <input class="span3 offset1 pull-right" type="text" name="Name" value="<?=$resource['Name']?>">
                </label>
                <label class="row">
                    <span class="span1 align-right">Company</span>
                    <input class="span3 offset1 pull-right" type="text" name="Company" value="<?=$resource['Company']?>">
                </label>
                <label class="row">
                    <span class="span1 align-right">Category</span>
                    <select class="span3 offset1 pull-right" name="Category">
                        <option value="Installer" <? if($resource['Category'] == 'Installer'){echo 'selected';} ?>>
                            Installer
                        </option>
                        <option value="Maintainer" <? if($resource['Category'] == 'Maintainer'){echo 'selected';} ?>>
                            Maintainer
                        </option>
                    </select>
                </label>
                <label class="row">
                    <span class="span1 align-right">Specialty</span>
                    <input class="span3 offset1 pull-right" type="text" name="Speciality" value="<?=$resource['Speciality']?>">
                </label>
            </div>
            <div class="span5 offset2">
                <label class="row">
                    <span class="span1 align-right">Address 1</span>
                    <input class="span3 offset1 pull-right" type="text" name="Address1" value="<?=$resource['Address1']?>">
                </label>
                <label class="row">
                    <span class="span1 align-right">Address 2</span>
                    <input class="span3 offset1 pull-right" type="text" name="Address2" value="<?=$resource['Address2']?>">
                </label>
                <label class="row">
                    <span class="span1 align-right">City</span>
                    <input class="span3 offset1 pull-right" type="text" name="City" value="<?=$resource['City']?>">
                </label>
                <label class="row">
                    <span class="span1 align-right">State</span>
                    <select class="span3 offset1 pull-right" name="State">
                        <? foreach($state_list as $abbr => $state) { ?>
                        <option value="<?=$abbr?>" <? if($resource['State'] == $abbr){echo 'selected';} ?>>
                            <?=$state?>
                        </option>
                        <? } ?>
                    </select>
                </label>
                <label class="row">
                    <span class="span1 align-right">Zip</span>
                    <input class="span3 offset1 pull-right" type="text" name="Zip" value="<?=$resource['Zip']?>">
                </label>
                <label class="row">
                    <span class="span1 align-right">Phone</span>
                    <input class="span3 offset1 pull-right" type="text" name="Phone" value="<?=$resource['Phone']?>">
                </label>
                <label class="row">
                    <span class="span1 align-right">Cell Phone</span>
                    <input class="span3 offset1 pull-right" type="text" name="PhoneMobile" value="<?=$resource['PhoneMobile']?>">
                </label>
            </div>
        </div>

        <br>
        <br>

        <div class="row">
            <div class="span6 offset3">
                <button class="btn btn-success pull-left" type="submit">
                    <i class="icon-ok icon-white"></i>
                    Save
                </button>
                <a href="../../" class="btn pull-right">
                    <i class="icon-remove"></i>
                    Cancel
                </a>
            </div>
        </div>

        </form>
<?php
require_once('../../../includes/footer.php');
?>
