<?php

/**
 *
 * ZPanel - A Cross-Platform Open-Source Web Hosting Control panel.
 * 
 * @package ZPanel
 * @version $Id$
 * @author Bobby Allen - ballen@zpanelcp.com
 * @copyright (c) 2008-2011 ZPanel Group - http://www.zpanelcp.com/
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License v3
 *
 * This program (ZPanel) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
class module_controller {

    static $complete;
    static $error;
    static $alreadyexists;
    static $badname;
    static $bademail;
    static $userblank;
    static $emailblank;
    static $packageblank;
    static $groupblank;
    static $ok;
    static $edit;
    static $clientid;
    static $clientpkgid;
	static $resetform;

    /**
     * The 'worker' methods.
     */
	 
    static function ListClients($uid) {
        global $zdbh;
        $sql = "SELECT * FROM x_accounts WHERE ac_reseller_fk=" . $uid . " AND ac_enabled_in=1 AND ac_deleted_ts IS NULL";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $res = array();
            $sql->execute();
            while ($rowclients = $sql->fetch()) {
                if ($rowclients['ac_user_vc'] != "zadmin") {
                    $currentuser = ctrl_users::GetUserDetail($rowclients['ac_id_pk']);
                    $currentuser['diskspacereadable'] = fs_director::ShowHumanFileSize(fs_director::GetQuotaUsages('diskspace', $currentuser['userid']));
                    $currentuser['diskspacequotareadable'] = fs_director::ShowHumanFileSize($currentuser['diskquota']);
                    $currentuser['bandwidthreadable'] = fs_director::ShowHumanFileSize(fs_director::GetQuotaUsages('bandwidth', $currentuser['userid']));
                    $currentuser['bandwidthquotareadable'] = fs_director::ShowHumanFileSize($currentuser['bandwidthquota']);
                    array_push($res, $currentuser);
                }
            }
            return $res;
        } else {
            return false;
        }
    }

    static function ListDisabledClients($uid) {
        global $zdbh;
        $sql = "SELECT * FROM x_accounts WHERE ac_reseller_fk=" . $uid . " AND ac_enabled_in=0 AND ac_deleted_ts IS NULL";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $res = array();
            $sql->execute();
            while ($rowclients = $sql->fetch()) {
                if ($rowclients['ac_user_vc'] != "zadmin") {
                    $currentuser = ctrl_users::GetUserDetail($rowclients['ac_id_pk']);
                    $currentuser['diskspacereadable'] = fs_director::ShowHumanFileSize(fs_director::GetQuotaUsages('diskspace', $currentuser['userid']));
                    $currentuser['diskspacequotareadable'] = fs_director::ShowHumanFileSize($currentuser['diskquota']);
                    $currentuser['bandwidthreadable'] = fs_director::ShowHumanFileSize(fs_director::GetQuotaUsages('bandwidth', $currentuser['userid']));
                    $currentuser['bandwidthquotareadable'] = fs_director::ShowHumanFileSize($currentuser['bandwidthquota']);
                    array_push($res, $currentuser);
                }
            }
            return $res;
        } else {
            return false;
        }
    }

    static function ListCurrentClient($uid) {
        $res = array();
        $currentuser = ctrl_users::GetUserDetail($uid);
        array_push($res, $currentuser);
        return $res;
    }

    static function ListGroups($uid) {
        global $zdbh;
        $currentuser = ctrl_users::GetUserDetail($uid);
        $sql = "SELECT * FROM x_groups WHERE ug_reseller_fk=" . $currentuser['resellerid'] . "";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $res = array();
            $sql->execute();
            while ($rowgroups = $sql->fetch()) {
                array_push($res, array('groupid' => $rowgroups['ug_id_pk'],
                    'groupname' => ui_language::translate($rowgroups['ug_name_vc'])));
            }
            return $res;
        } else {
            return false;
        }
    }

    static function ListCurrentGroups($uid, $rid) {
        global $zdbh;
        $sql = "SELECT * FROM x_groups WHERE ug_reseller_fk=" . $rid . "";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $currentuser = ctrl_users::GetUserDetail($uid);
            $sql = $zdbh->prepare($sql);
            $res = array();
            $sql->execute();
            while ($rowgroups = $sql->fetch()) {
                $selected = "";
                if ($rowgroups['ug_id_pk'] == $currentuser['usergroupid']) {
                    $selected = " selected";
                }
                array_push($res, array('groupid' => $rowgroups['ug_id_pk'],
                    'groupname' => ui_language::translate($rowgroups['ug_name_vc']),
                    'groupselected' => $selected));
            }
            return $res;
        } else {
            return false;
        }
    }

    static function ListPackages($uid) {
        global $zdbh;
        $sql = "SELECT * FROM x_packages WHERE pk_reseller_fk=" . $uid . " AND pk_deleted_ts IS NULL";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $res = array();
            $sql->execute();
            while ($rowgroups = $sql->fetch()) {
                array_push($res, array('packageid' => $rowgroups['pk_id_pk'],
                    'packagename' => ui_language::translate($rowgroups['pk_name_vc'])));
            }
            return $res;
        } else {
            return false;
        }
    }

    static function ListCurrentPackages($uid, $rid) {
        global $zdbh;
        $sql = "SELECT * FROM x_packages WHERE pk_reseller_fk=" . $rid . " AND pk_deleted_ts IS NULL";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $currentuser = ctrl_users::GetUserDetail($uid);
            $sql = $zdbh->prepare($sql);
            $res = array();
            $sql->execute();
            while ($rowgroups = $sql->fetch()) {
                $selected = "";
                if ($rowgroups['pk_id_pk'] == $currentuser['packageid']) {
                    $selected = " selected";
                }
                array_push($res, array('packageid' => $rowgroups['pk_id_pk'],
                    'packagename' => ui_language::translate($rowgroups['pk_name_vc']),
                    'packageselected' => $selected));
            }
            return $res;
        } else {
            return false;
        }
    }

    static function SetClientAccount($userid, $column, $value) {
        global $zdbh;
        runtime_hook::Execute('OnBeforeSetClientAccount');
        $sql = $zdbh->prepare("UPDATE x_accounts
								SET " . $column . "=" . $value . " 
								WHERE ac_id_pk=" . $userid . "");
        $sql->execute();
        runtime_hook::Execute('OnAfterSetClientAccount');
        return true;
    }

    static function SetClientProfile($userid, $column, $value) {
        global $zdbh;
        runtime_hook::Execute('OnBeforeSetClientProfile');
        $sql = $zdbh->prepare("UPDATE x_profiles
								SET " . $column . "=" . $value . " 
								WHERE ud_user_fk=" . $userid . "");
        $sql->execute();
        runtime_hook::Execute('OnAfterSetClientProfile');
        return true;
    }

    static function ExecuteDeleteClient($userid) {
        global $zdbh;
        runtime_hook::Execute('OnBeforeDeleteClient');
        $sql = $zdbh->prepare("UPDATE x_accounts
								SET ac_deleted_ts=" . time() . " 
								WHERE ac_id_pk=" . $userid . "");
        $sql->execute();
        runtime_hook::Execute('OnAfterDeleteClient');
		self::$ok=true;
        return true;
    }

    static function ExecuteUpdateClient($clientid, $package, $enabled, $group, $fullname, $email, $address, $post, $phone, $newpass) {
        global $zdbh;
        runtime_hook::Execute('OnBeforeUpdateClient');
        $sql = $zdbh->prepare("UPDATE x_accounts SET 
										ac_package_fk= " . $package . " ,
										ac_enabled_in= " . $enabled . ",
                                        ac_group_fk=   " . $group . "
										WHERE ac_id_pk=" . $clientid . "");
        $sql->execute();

        $sql = $zdbh->prepare("UPDATE x_profiles SET 
										ud_fullname_vc= '" . $fullname . "',
                                        ud_email_vc=    '" . $email . "',
										ud_group_fk=    '" . $group . "',
										ud_package_fk=  '" . $package . "',
										ud_address_tx=  '" . $address . "',
										ud_postcode_vc= '" . $post . "',
										ud_phone_vc=    '" . $phone . "'
										WHERE ud_user_fk=" . $clientid . "");
        $sql->execute();
		if ($enabled == 0){
			self::DisableClient($clientid);
		}
		if ($enabled == 1){
			self::EnableClient($clientid);		
		}
        runtime_hook::Execute('OnAfterUpdateClient');
		self::$ok=true;
        return true;
    }

    static function EnableClient($userid) {
        runtime_hook::Execute('OnBeforeEnableClient');
        global $zdbh;
        $sql = $zdbh->prepare("UPDATE x_accounts SET ac_enabled_in=1 WHERE ac_id_pk=" . $userid . "");
        $sql->execute();
        runtime_hook::Execute('OnAfterEnableClient');
        return true;
    }

    static function DisableClient($userid) {
        runtime_hook::Execute('OnBeforeDisableClient');
        global $zdbh;
        $sql = $zdbh->prepare("UPDATE x_accounts SET ac_enabled_in=0 WHERE ac_id_pk=" . $userid . "");
        $sql->execute();
        runtime_hook::Execute('OnAfterDisableClient');
        return true;
    }

    static function CheckEnabledHTML($userid) {
        $currentuser = ctrl_users::GetUserDetail($userid);
        $res = array();
        if ($currentuser['enabled'] == 1) {
            $echecked = "CHECKED";
            $dchecked = "";
        } else {
            $echecked = "";
            $dchecked = "CHECKED";
        }
        array_push($res, array('echecked' => $echecked,
            'dchecked' => $dchecked));
        return $res;
    }

    static function ExecuteCreateClient($uid, $username, $packageid, $groupid, $fullname, $email, $address, $post, $phone, $password) {
        global $zdbh;
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        // Check for spaces and remove if found...
        $username = strtolower(str_replace(' ', '', $username));
        // Check for errors before we continue...
        if (fs_director::CheckForEmptyValue(self::CheckCreateForErrors($username, $packageid, $groupid, $email))) {
            return false;
        }
        runtime_hook::Execute('OnBeforeCreateClient');
        // No errors found, so we can add the user to the database...
        $sql = $zdbh->prepare("INSERT INTO x_accounts (
										ac_user_vc,
										ac_pass_vc,
										ac_package_fk,
                                        ac_group_fk,
										ac_reseller_fk,
										ac_created_ts) VALUES (
										'" . $username . "',
										'" . md5($password) . "',
                                        '" . $packageid . "',
										'" . $groupid . "',
										" . $uid . ",
										" . time() . ")");
        $sql->execute();
        // Now lets pull back the client ID so that we can add their personal address details etc...
        $client = $zdbh->query("SELECT * FROM x_accounts WHERE ac_reseller_fk=" . $uid . " ORDER BY ac_id_pk DESC")->Fetch();
        $sql = $zdbh->prepare("INSERT INTO x_profiles (ud_user_fk,
										ud_fullname_vc,
										ud_email_vc,
										ud_group_fk,
										ud_package_fk,
										ud_address_tx,
										ud_postcode_vc,
										ud_phone_vc,
										ud_created_ts) VALUES (
										 " . $client['ac_id_pk'] . ",
										'" . $fullname . "',
										'" . $email . "',
										'" . $packageid . "',
										'" . $groupid . "',
										'" . $address . "',
										'" . $post . "',
										'" . $phone . "',
										 " . time() . ")");
        $sql->execute();
        // Now we add an entry into the bandwidth table, for the user for the upcoming month.
        $sql = $zdbh->prepare("INSERT INTO x_bandwidth (bd_acc_fk, bd_month_in, bd_transamount_bi, bd_diskamount_bi) VALUES (" . $client['ac_id_pk'] . "," . date("Ym", time()) . ", 0, 0)");
        $sql->execute();
        // Lets create the client diectories
        fs_filehandler::CreateDirectory(ctrl_options::GetOption('hosted_dir') . $username);
		fs_director::SetDirectoryPermissions(ctrl_options::GetOption('hosted_dir') . $username, 0777);
		fs_filehandler::CreateDirectory(ctrl_options::GetOption('hosted_dir') . $username . "/public_html");
		fs_director::SetDirectoryPermissions(ctrl_options::GetOption('hosted_dir') . $username . "/public_html", 0777);
		fs_filehandler::CreateDirectory(ctrl_options::GetOption('hosted_dir') . $username . "/backups");
		fs_director::SetDirectoryPermissions(ctrl_options::GetOption('hosted_dir') . $username . "/backups", 0777);
        runtime_hook::Execute('OnAfterCreateClient');
		self::$resetform=true;
		self::$ok=true;
		return true;

        // Create the MySQL account for the user...
        // Now we create the user's home directory if it doesnt already exsist...
        // Create the domain logs folder read for Apache...
        // Create a default FTP account if set in the system options...
        // Send the user account details via. email (if requested)...
    }

    static function CheckCreateForErrors($username, $packageid, $groupid, $email) {
        global $zdbh;
        $username = strtolower(str_replace(' ', '', $username));
        // Check to make sure the username is not blank or exists before we go any further...
        if (!fs_director::CheckForEmptyValue($username)) {
            $sql = "SELECT COUNT(*) FROM x_accounts WHERE UPPER(ac_user_vc)='" . strtoupper($username) . "' AND ac_deleted_ts IS NULL";
            if ($numrows = $zdbh->query($sql)) {
                if ($numrows->fetchColumn() <> 0) {
                    self::$alreadyexists = true;
                    return false;
                }
            }
            if (!self::IsValidUserName($username)) {
                self::$badname = true;
                return false;
            }
        } else {
            self::$userblank = true;
            return false;
        }
        // Check to make sure the packagename is not blank and exists before we go any further...
        if (!fs_director::CheckForEmptyValue($packageid)) {
            $sql = "SELECT COUNT(*) FROM x_packages WHERE pk_id_pk='" . $packageid . "' AND pk_deleted_ts IS NULL";
            if ($numrows = $zdbh->query($sql)) {
                if ($numrows->fetchColumn() == 0) {
                    self::$packageblank = true;
                    return false;
                }
            }
        } else {
            self::$packageblank = true;
            return false;
        }
        // Check to make sure the groupname is not blank and exists before we go any further...
        if (!fs_director::CheckForEmptyValue($packageid)) {
            $sql = "SELECT COUNT(*) FROM x_groups WHERE ug_id_pk='" . $groupid . "'";
            if ($numrows = $zdbh->query($sql)) {
                if ($numrows->fetchColumn() == 0) {
                    self::$groupblank = true;
                    return;
                }
            }
        } else {
            self::$groupblank = true;
            return false;
        }
        // Check for invalid characters in the email and that it exists...
        if (!fs_director::CheckForEmptyValue($email)) {
            if (!self::IsValidEmail($email)) {
                self::$bademail = true;
                return false;
            }
        } else {
            self::$emailblank = true;
            return false;
        }

        return true;
    }

    static function IsValidEmail($email) {
        if (!preg_match('/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i', $email)) {
            return false;
        }
        return true;
    }

    static function IsValidUserName($username) {
        if (!preg_match('/^[a-z\d][a-z\d-]{0,62}$/i', $username) || preg_match('/-$/', $username)) {
            return false;
        }
        return true;
    }

    /**
     * End 'worker' methods.
     */

    /**
     * Webinterface sudo methods.
     */
	 
    static function doCreateClient() {
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (self::ExecuteCreateClient($currentuser['userid'], $formvars['inNewUserName'], $formvars['inNewPackage'], $formvars['inNewGroup'], $formvars['inNewFullName'], $formvars['inNewEmailAddress'], $formvars['inNewAddress'], $formvars['inNewPostCode'], $formvars['inNewPhone'], $formvars['inNewPassword'])){
			unset($_POST['inNewUserName']);
            return true;
		} else {
        return false;
		}
    }

    static function doEditClient() {
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        $formvars = $controller->GetAllControllerRequests('FORM');
        foreach (self::ListClients($currentuser['userid']) as $row) {
            if (isset($formvars['inDelete_' . $row['userid'] . ''])) {
                header("location: ./?module=" . $controller->GetCurrentModule() . "&show=Delete&other=" . $row['userid'] . "");
                exit;
            }
            if (isset($formvars['inEdit_' . $row['userid'] . ''])) {
                header("location: ./?module=" . $controller->GetCurrentModule() . "&show=Edit&other=" . $row['userid'] . "");
                exit;
            }
        }
        return;
    }

    static function doEditDisabledClient() {
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        $formvars = $controller->GetAllControllerRequests('FORM');
        foreach (self::ListDisabledClients($currentuser['userid']) as $row) {
            if (isset($formvars['inDelete_' . $row['userid'] . ''])) {
                header("location: ./?module=" . $controller->GetCurrentModule() . "&show=Delete&other=" . $row['userid'] . "");
                exit;
            }
            if (isset($formvars['inEdit_' . $row['userid'] . ''])) {
                header("location: ./?module=" . $controller->GetCurrentModule() . "&show=Edit&other=" . $row['userid'] . "");
                exit;
            }
        }
        return;
    }

    static function doDeleteClient() {
        global $controller;
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (self::ExecuteDeleteClient($formvars['inDelete']))
            return true;
        return false;
    }

    static function doUpdateClient() {
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (self::ExecuteUpdateClient($formvars['inClientID'], $formvars['inPackage'], $formvars['inEnabled'], $formvars['inGroup'], $formvars['inFullName'], $formvars['inEmailAddress'], $formvars['inAddress'], $formvars['inPostCode'], $formvars['inPhone'], $formvars['inNewPassword']))
            return true;
        return false;
    }

    static function getClientList() {
        $currentuser = ctrl_users::GetUserDetail();
        $clientlist = self::ListClients($currentuser['userid']);
        if (!fs_director::CheckForEmptyValue($clientlist)) {
            return $clientlist;
        } else {
            return false;
        }
    }

    static function getDisabledClientList() {
        $currentuser = ctrl_users::GetUserDetail();
        $disabledclientlist = self::ListDisabledClients($currentuser['userid']);
        if (!fs_director::CheckForEmptyValue($disabledclientlist)) {
            return $disabledclientlist;
        } else {
            return false;
        }
    }

    static function getCurrentClient() {
        global $controller;
        $urlvars = $controller->GetAllControllerRequests('URL');
        $client = self::ListCurrentClient($urlvars['other']);
        if (!fs_director::CheckForEmptyValue($client)) {
            return $client;
        } else {
            return false;
        }
    }

    static function getGroupList() {
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        return self::ListGroups($currentuser['userid']);
    }

    static function getCurrentGroupList() {
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        return self::ListCurrentGroups($controller->GetControllerRequest('URL', 'other'), $currentuser['userid']);
    }

    static function getPackageList() {
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        return self::ListPackages($currentuser['userid']);
    }

    static function getCurrentPackageList() {
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        return self::ListCurrentPackages($controller->GetControllerRequest('URL', 'other'), $currentuser['userid']);
    }

    static function getCheckEnabledHTML() {
        global $controller;
        return self::CheckEnabledHTML($controller->GetControllerRequest('URL', 'other'));
    }

    static function getisCreateClient() {
        global $controller;
        $urlvars = $controller->GetAllControllerRequests('URL');
        if (!isset($urlvars['show']))
            return true;
        return false;
    }

    static function getisDeleteClient() {
        global $controller;
        $urlvars = $controller->GetAllControllerRequests('URL');
        if ((isset($urlvars['show'])) && ($urlvars['show'] == "Delete"))
            return true;
        return false;
    }

    static function getisEditClient() {
        global $controller;
        $urlvars = $controller->GetAllControllerRequests('URL');
        if ((isset($urlvars['show'])) && ($urlvars['show'] == "Edit")) {
            return true;
        } else {
            return false;
        }
    }

    static function getEditCurrentName() {
        global $controller;
        if ($controller->GetControllerRequest('URL', 'other')) {
            $current = self::ListCurrentClient($controller->GetControllerRequest('URL', 'other'));
            return $current[0]['username'];
        } else {
            return "";
        }
    }

    static function getEditCurrentEmail() {
        global $controller;
        if ($controller->GetControllerRequest('URL', 'other')) {
            $current = self::ListCurrentClient($controller->GetControllerRequest('URL', 'other'));
            return $current[0]['email'];
        } else {
            return "";
        }
    }

    static function getEditCurrentFullName() {
        global $controller;
        if ($controller->GetControllerRequest('URL', 'other')) {
            $current = self::ListCurrentClient($controller->GetControllerRequest('URL', 'other'));
            return $current[0]['fullname'];
        } else {
            return "";
        }
    }

    static function getEditCurrentPost() {
        global $controller;
        if ($controller->GetControllerRequest('URL', 'other')) {
            $current = self::ListCurrentClient($controller->GetControllerRequest('URL', 'other'));
            return $current[0]['postcode'];
        } else {
            return "";
        }
    }

    static function getEditCurrentID() {
        global $controller;
        if ($controller->GetControllerRequest('URL', 'other')) {
            $current = self::ListCurrentClient($controller->GetControllerRequest('URL', 'other'));
            return $current[0]['userid'];
        } else {
            return "";
        }
    }

    static function getEditCurrentAddress() {
        global $controller;
        if ($controller->GetControllerRequest('URL', 'other')) {
            $current = self::ListCurrentClient($controller->GetControllerRequest('URL', 'other'));
            return $current[0]['address'];
        } else {
            return "";
        }
    }

    static function getEditCurrentPhone() {
        global $controller;
        if ($controller->GetControllerRequest('URL', 'other')) {
            $current = self::ListCurrentClient($controller->GetControllerRequest('URL', 'other'));
            return $current[0]['phone'];
        } else {
            return "";
        }
    }

    static function getFormName() {
        global $controller;
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (isset($formvars['inNewUserName']) && fs_director::CheckForEmptyValue(self::$resetform)) {
            return $formvars['inNewUserName'];
        }
        return;
    }

    static function getFormFullName() {
        global $controller;
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (isset($formvars['inNewFullName']) && fs_director::CheckForEmptyValue(self::$resetform)) {
            return $formvars['inNewFullName'];
        }
        return;
    }

    static function getFormEmail() {
        global $controller;
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (isset($formvars['inNewEmailAddress']) && fs_director::CheckForEmptyValue(self::$resetform)) {
            return $formvars['inNewEmailAddress'];
        }
        return;
    }

    static function getFormAddress() {
        global $controller;
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (isset($formvars['inNewAddress']) && fs_director::CheckForEmptyValue(self::$resetform)) {
            return $formvars['inNewAddress'];
        }
        return;
    }

    static function getFormPost() {
        global $controller;
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (isset($formvars['inNewPostCode']) && fs_director::CheckForEmptyValue(self::$resetform)) {
            return $formvars['inNewPostCode'];
        }
        return;
    }

    static function getFormPhone() {
        global $controller;
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (isset($formvars['inNewPhone']) && fs_director::CheckForEmptyValue(self::$resetform)) {
            return $formvars['inNewPhone'];
        }
        return;
    }

    static function getModuleName() {
        $module_name = ui_module::GetModuleName();
        return $module_name;
    }

    static function getModuleIcon() {
        global $controller;
        $module_icon = "modules/" . $controller->GetControllerRequest('URL', 'module') . "/assets/icon.png";
        return $module_icon;
    }

    static function getModuleDesc() {
        $message = ui_language::translate(ui_module::GetModuleDescription());
        return $message;
    }

    static function getRandomPassword() {
        $password = fs_director::GenerateRandomPassword(9, 4);
        return $password;
    }

    static function getResult() {
        if (!fs_director::CheckForEmptyValue(self::$userblank)) {
            return ui_sysmessage::shout(ui_language::translate("You need to specify a username to create a new client."), "zannounceerror");
        }
        if (!fs_director::CheckForEmptyValue(self::$emailblank)) {
            return ui_sysmessage::shout(ui_language::translate("You need to specify an email address to create a new client."), "zannounceerror");
        }
        if (!fs_director::CheckForEmptyValue(self::$packageblank)) {
            return ui_sysmessage::shout(ui_language::translate("You must select a package for your new client."), "zannounceerror");
        }
        if (!fs_director::CheckForEmptyValue(self::$groupblank)) {
            return ui_sysmessage::shout(ui_language::translate("You must select a user group for your new client."), "zannounceerror");
        }
        if (!fs_director::CheckForEmptyValue(self::$badname)) {
            return ui_sysmessage::shout(ui_language::translate("Your client name is not valid. Please enter a valid client name."), "zannounceerror");
        }
        if (!fs_director::CheckForEmptyValue(self::$bademail)) {
            return ui_sysmessage::shout(ui_language::translate("Your email adress is not valid. Please enter a valid email address."), "zannounceerror");
        }
        if (!fs_director::CheckForEmptyValue(self::$alreadyexists)) {
            return ui_sysmessage::shout(ui_language::translate("A client with that name already appears to exsist on this server."), "zannounceerror");
        }
        if (!fs_director::CheckForEmptyValue(self::$ok)) {
            return ui_sysmessage::shout(ui_language::translate("Changes to your client(s) have been saved successfully!"), "zannounceok");
        }
        return;
    }

    /**
     * Webinterface sudo methods.
     */
}

?>
