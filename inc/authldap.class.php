<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

/**
 *  Class used to manage Auth LDAP config
**/
class AuthLDAP extends CommonDBTM {

   static function getTypeName() {
      global $LANG;

      return $LANG['login'][2];
   }

   function canCreate() {
      return haveRight('config', 'w');
   }

   function canView() {
      return haveRight('config', 'r');
   }

   function post_getEmpty () {
      $this->fields['port']='389';
      $this->fields['condition']='';
      $this->fields['login_field']='uid';
      $this->fields['use_tls']=0;
      $this->fields['group_field']='';
      $this->fields['group_condition']='';
      $this->fields['group_search_type']=0;
      $this->fields['group_member_field']='';
      $this->fields['email_field']='mail';
      $this->fields['realname_field']='cn';
      $this->fields['firstname_field']='givenname';
      $this->fields['phone_field']='telephonenumber';
      $this->fields['phone2_field']='';
      $this->fields['mobile_field']='';
      $this->fields['comment_field']='';
      $this->fields['title_field']='';
      $this->fields['use_dn']=0;
   }

   /**
    * Preconfig datas for standard system
    * @param $type type of standard system : AD
    *@return nothing
    **/
   function preconfig($type) {
      switch($type) {
         case 'AD' :
            $this->fields['port']="389";
            $this->fields['condition']=
               '(&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))';
            $this->fields['login_field']='samaccountname';
            $this->fields['use_tls']=0;
            $this->fields['group_field']='memberof';
            $this->fields['group_condition']=
               '(&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))';
            $this->fields['group_search_type']=0;
            $this->fields['group_member_field']='';
            $this->fields['email_field']='mail';
            $this->fields['realname_field']='sn';
            $this->fields['firstname_field']='givenname';
            $this->fields['phone_field']='telephonenumber';
            $this->fields['phone2_field']='othertelephone';
            $this->fields['mobile_field']='mobile';
            $this->fields['comment_field']='info';
            $this->fields['title_field']='title';
            $this->fields['entity_field']='ou';
            $this->fields['entity_condition']='(objectclass=organizationalUnit)';
            $this->fields['use_dn']=1;
            break;

         default:
            $this->post_getEmpty();
            break;
      }
   }
   function prepareInputForUpdate($input) {
      if (isset($input["rootdn_password"]) && empty($input["rootdn_password"])) {
         unset($input["rootdn_password"]);
      }
      return $input;
   }

   /**
    * Print the auth ldap form
    *
   * @param $options array
   *     - target for the Form
    *
    *@return Nothing (display)
    **/
   function showForm($ID, $options=array()) {
      global $LANG;

      if (!haveRight("config", "w")) {
         return false;
      }
      $spotted = false;
      if (empty ($ID)) {
         if ($this->getEmpty()) {
            $spotted = true;
         }
         if (isset($_GET['preconfig'])) {
            $this->preconfig($_GET['preconfig']);
         }
      } else {
         if ($this->getFromDB($ID)) {
            $spotted = true;
         }
      }

      if (canUseLdap()) {
         $this->showTabs($options);
         $this->showFormHeader($options);
         if (empty($ID)) {
            $target = $_SERVER['PHP_SELF'];
            echo "<tr class='tab_bg_2'><td>".$LANG['ldap'][16]."&nbsp;:</td> ";
            echo "<td colspan='3'>";
            echo "<a href='$target?preconfig=AD'>".$LANG['ldap'][17]."</a>";
            echo "&nbsp;&nbsp;/&nbsp;&nbsp;";
            echo "<a href='$target?preconfig=default'>".$LANG['common'][44];
            echo "</a></td></tr>";
         }
         echo "<tr class='tab_bg_1'><td>" . $LANG['common'][16] . "&nbsp;:</td>";
         echo "<td><input type='text' name='name' value='". $this->fields["name"] ."'></td><td>";
         echo ($ID>0?$LANG['common'][26]."&nbsp;:</td><td>".convDateTime($this->fields["date_mod"]):'&nbsp;');
         echo "</td></tr>";

         echo "<tr class='tab_bg_1'><td>" . $LANG['common'][52] . "&nbsp;:</td>";
         echo "<td><input type='text' name='host' value='" . $this->fields["host"] . "'></td>";
         echo "<td>" . $LANG['setup'][172] . "&nbsp;:</td>";
         echo "<td><input id='port' type='text' name='port' value='" . $this->fields["port"] . "'>";
         echo "</td></tr>";

         echo "<tr class='tab_bg_1'><td>" . $LANG['setup'][154] . "&nbsp;:</td>";
         echo "<td><input type='text' name='basedn' value='" . $this->fields["basedn"] . "'>";
         echo "</td>";
         echo "<td>" . $LANG['setup'][155] . "&nbsp;:</td>";
         echo "<td><input type='text' name='rootdn' value='" . $this->fields["rootdn"] . "'>";
         echo "</td></tr>";

         echo "<tr class='tab_bg_1'><td>" . $LANG['setup'][156] . "&nbsp;:</td>";
         echo "<td><input type='password' name='rootdn_password' value=''></td>";
         echo "<td>" . $LANG['setup'][228] . "&nbsp;:</td>";
         echo "<td><input type='text' name='login_field' value='".$this->fields["login_field"]."'>";
         echo "</td></tr>";

         echo "<tr class='tab_bg_1'><td>" . $LANG['setup'][159] . "&nbsp;:</td>";
         echo "<td colspan='3'><input type='text' name='condition' value='".
                                 $this->fields["condition"]."' size='100'></td></tr>";

         echo "<tr class='tab_bg_1'><td>" . $LANG['common'][25] . "&nbsp;:</td>";
         echo "<td colspan='3'>";
         echo "<textarea cols='40' name='comment'>".$this->fields["comment"]."</textarea>";
         echo "</td></tr>";

         //Fill fields when using preconfiguration models
         if (!$ID) {
            $hidden_fields = array ('port', 'condition' , 'login_field', 'use_tls', 'group_field',
                                    'group_condition', 'group_search_type', 'group_member_field',
                                    'email_field', 'realname_field', 'firstname_field',
                                    'phone_field', 'phone2_field', 'mobile_field', 'comment_field',
                                    'title_field', 'use_dn', 'entity_field', 'entity_condition');

            foreach ($hidden_fields as $hidden_field) {
               echo "<input type='hidden' name='$hidden_field' value='".
                     $this->fields[$hidden_field]."'>";
            }
         }

         $this->showFormButtons($options);

         echo "<div id='tabcontent'></div>";
         echo "<script type='text/javascript'>loadDefaultTab();</script>";
      }
   }

   function showFormAdvancedConfig($ID, $target) {
      global $LANG, $CFG_GLPI, $DB;

      echo "<form method='post' action='$target'>";
      echo "<div class='center'><table class='tab_cadre_fixe'>";

      echo "<tr class='tab_bg_2'><th colspan='4'>";
      echo "<input type='hidden' name='id' value='$ID'>";
      echo $LANG['entity'][14] . "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $LANG['setup'][180] . "&nbsp;:</td><td>";
      if (function_exists("ldap_start_tls")) {
         $use_tls = $this->fields["use_tls"];
         echo "<select name='use_tls'>";
         echo "<option value='0' " . (!$use_tls ? " selected " : "") . ">" . $LANG['choice'][0] .
               "</option>";
         echo "<option value='1' " . ($use_tls ? " selected " : "") . ">" . $LANG['choice'][1] .
               "</option>";
         echo "</select>";
      } else {
         echo "<input type='hidden' name='use_tls' value='0'>";
         echo $LANG['setup'][181];
      }
      echo "</td>";
      echo "<td>" . $LANG['setup'][186] . "&nbsp;:</td><td>";
      Dropdown::showGMT("time_offset",$this->fields["time_offset"]);
      echo"</td></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $LANG['ldap'][30] . "&nbsp;:</td><td colspan='3'>";
      $alias_options[LDAP_DEREF_NEVER] = $LANG['ldap'][31];
      $alias_options[LDAP_DEREF_ALWAYS] = $LANG['ldap'][32];
      $alias_options[LDAP_DEREF_SEARCHING] = $LANG['ldap'][33];
      $alias_options[LDAP_DEREF_FINDING] = $LANG['ldap'][34];
      Dropdown::showFromArray("deref_option",$alias_options,
                     array('value' => $this->fields["deref_option"]));
      echo"</td></tr>";
      echo "<tr class='tab_bg_2'><td class='center' colspan=4>";
      echo "<input type='submit' name='update' class='submit' value='".
                $LANG['buttons'][2]."'></td>";
      echo "</td></tr>";
      echo "</table></form></div>";
   }

   function showFormReplicatesConfig($ID, $target) {
      global $LANG, $CFG_GLPI, $DB;

      AuthLdapReplicate::addNewReplicateForm($target, $ID);

      $sql = "SELECT *
              FROM `glpi_authldapreplicates`
              WHERE `authldaps_id` = '".$ID."'
              ORDER BY `name`";
      $result = $DB->query($sql);

      if ($DB->numrows($result) >0) {
         echo "<br>";
         $canedit = haveRight("config", "w");
         echo "<form action='$target' method='post' name='ldap_replicates_form'
                id='ldap_replicates_form'>";
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";

         echo "<input type='hidden' name='id' value='$ID'>";
         echo "<tr><th colspan='4'>".$LANG['ldap'][18] . "</th></tr>";

         if (isset($_SESSION["LDAP_TEST_MESSAGE"])) {
            echo "<tr class='tab_bg_2'><td class='center' colspan=4>";
            echo $_SESSION["LDAP_TEST_MESSAGE"];
            echo"</td></tr>";
            unset($_SESSION["LDAP_TEST_MESSAGE"]);
         }

         echo "<tr class='tab_bg_2'><td></td>";
         echo "<td class='center b'>".$LANG['common'][16]."</td>";
         echo "<td class='center b'>".$LANG['ldap'][18]."</td><td class='center'></td></tr>";
         while ($ldap_replicate = $DB->fetch_array($result)) {
            echo "<tr class='tab_bg_1'><td class='center' width='10'>";
            if (isset ($_GET["select"]) && $_GET["select"] == "all") {
               $sel = "checked";
            }
            $sel ="";
            echo "<input type='checkbox' name='item[" . $ldap_replicate["id"] . "]'
                   value='1' $sel>";
            echo "</td>";
            echo "<td class='center'>" . $ldap_replicate["name"] . "</td>";
            echo "<td class='center'>".$ldap_replicate["host"]." : ".$ldap_replicate["port"] . "</td>";
            echo "<td class='center'>";
            echo "<input type='submit' name='test_ldap_replicate[".$ldap_replicate["id"]."]'
                  class='submit' value=\"" . $LANG['buttons'][50] . "\" ></td>";
            echo"</tr>";
         }
         echo "</table>";

         openArrowMassive("ldap_replicates_form", true);
         closeArrowMassive('delete_replicate', $LANG['buttons'][6]);

         echo "</div></form>";
      }
   }

   function showFormGroupsConfig($ID, $target) {
      global $LANG,$CFG_GLPI;

      echo "<form method='post' action='$target'>";
      echo "<div class='center'><table class='tab_cadre_fixe'>";
      echo "<input type='hidden' name='id' value='$ID'>";

      echo "<th class='center' colspan='4'>" . $LANG['setup'][259] . "</th></tr>";

      echo "<tr class='tab_bg_1'><td>" . $LANG['setup'][254] . "&nbsp;:</td><td>";
      $group_search_type = $this->fields["group_search_type"];
      echo "<select name='group_search_type'>";
      echo "<option value='0' " . (($group_search_type == 0) ? " selected " : "") . ">" .
             $LANG['setup'][256] . "</option>";
      echo "<option value='1' " . (($group_search_type == 1) ? " selected " : "") . ">" .
             $LANG['setup'][257] . "</option>";
      echo "<option value='2' " . (($group_search_type == 2) ? " selected " : "") . ">" .
             $LANG['setup'][258] . "</option>";
      echo "</select></td>";
      echo "<td>" . $LANG['setup'][260] . "&nbsp;:</td>";
      echo "<td><input type='text' name='group_field' value='".$this->fields["group_field"]."'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . $LANG['setup'][253] . "&nbsp;:</td><td colspan='3'>";
      echo "<input type='text' name='group_condition' value='".
             $this->fields["group_condition"]."' size='100'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . $LANG['setup'][255] . "&nbsp;:</td>";
      echo "<td><input type='text' name='group_member_field' value='".
                 $this->fields["group_member_field"]."'></td>";

      echo "<td>" . $LANG['setup'][262] . "&nbsp;:</td>";
      echo "<td>";
      Dropdown::showYesNo("use_dn",$this->fields["use_dn"]);
      echo "</td></tr>";
      echo "<tr class='tab_bg_2'><td class='center' colspan=4>";
      echo "<input type='submit' name='update' class='submit' value='".
                $LANG['buttons'][2]."'></td>";
      echo "</td></tr>";
      echo "</table></form></div>";
   }

   function showFormTestLDAP ($ID, $target) {
      global $LANG,$CFG_GLPI;

      echo "<form method='post' action='$target'>";
      echo "<div class='center'><table class='tab_cadre_fixe'>";
      echo "<input type='hidden' name='id' value='$ID'>";
      echo "<tr><th colspan='4'>" . $LANG['ldap'][9] . "</th></tr>";
      if (isset($_SESSION["LDAP_TEST_MESSAGE"])) {
         echo "<tr class='tab_bg_2'><td class='center' colspan=4>";
         echo $_SESSION["LDAP_TEST_MESSAGE"];
         echo"</td></tr>";
         unset($_SESSION["LDAP_TEST_MESSAGE"]);
      }
      echo "<tr class='tab_bg_2'><td class='center' colspan=4>";
      echo "<input type='submit' name='test_ldap' class='submit' value='".
            $LANG['buttons'][2]."'></td></tr>";
      echo "</table></div>";
   }

   function showFormUserConfig($ID,$target) {
      global $LANG,$CFG_GLPI;

      echo "<form method='post' action='$target'>";
      echo "<div class='center'><table class='tab_cadre_fixe'>";
      echo "<input type='hidden' name='id' value='$ID'>";

      echo "<tr class='tab_bg_1'>";
      echo "<th class='center' colspan='4'>" . $LANG['setup'][167] . "</th></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['common'][48] . "&nbsp;:</td>";
      echo "<td><input type='text' name='realname_field' value='".
                 $this->fields["realname_field"]."'></td>";
      echo "<td>" . $LANG['common'][43] . "&nbsp;:</td>";
      echo "<td><input type='text' name='firstname_field' value='".
                 $this->fields["firstname_field"]."'></td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['common'][25] . "&nbsp;:</td>";
      echo "<td><input type='text' name='comment_field' value='".
                 $this->fields["comment_field"]."'></td>";
      echo "<td>" . $LANG['setup'][14] . "&nbsp;:</td>";
      echo "<td><input type='text' name='email_field' value='".$this->fields["email_field"]."'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['help'][35] . "&nbsp;:</td>";
      echo "<td><input type='text' name='phone_field'value='".$this->fields["phone_field"]."'>";
      echo "</td>";
      echo "<td>" . $LANG['help'][35] . " 2 &nbsp;:</td>";
      echo "<td><input type='text' name='phone2_field'value='".$this->fields["phone2_field"]."'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['common'][42] . "&nbsp;:</td>";
      echo "<td><input type='text' name='mobile_field'value='".$this->fields["mobile_field"]."'>";
      echo "</td>";
      echo "<td>" . $LANG['users'][1] . "&nbsp;:</td>";
      echo "<td><input type='text' name='title_field' value='".$this->fields["title_field"]."'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['users'][2] . "&nbsp;:</td>";
      echo "<td><input type='text' name='category_field' value='".
                 $this->fields["category_field"]."'></td>";
      echo "<td>" . $LANG['setup'][41] . "&nbsp;:</td>";
      echo "<td><input type='text' name='language_field' value='".
                 $this->fields["language_field"]. "'></td></tr>";
      echo "<tr class='tab_bg_2'><td class='center' colspan=4>";
      echo "<input type='submit' name='update' class='submit' value='".
                $LANG['buttons'][2]."'></td>";
      echo "</td></tr>";
      echo "</table></form></div>";
   }


   function showFormEntityConfig($ID, $target) {
      global $LANG,$CFG_GLPI;
      echo "<form method='post' action='$target'>";
      echo "<div class='center'><table class='tab_cadre_fixe'>";
      echo "<input type='hidden' name='id' value='$ID'>";

      echo "<th class='center' colspan='4'>" . $LANG['setup'][623] . "</th></tr>";

      echo "<tr class='tab_bg_1'><td>" . $LANG['setup'][621] . "&nbsp;:</td>";
      echo "<td colspan='3'><input type='text' name='entity_field' value='".
         $this->fields["entity_field"]."'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . $LANG['setup'][622] . "&nbsp;:</td>";
      echo "<td colspan='3'><input type='text' name='entity_condition' value='".
             $this->fields["entity_condition"]."' size='100'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td class='center' colspan=4>";
      echo "<input type='submit' name='update' class='submit' value='".
                $LANG['buttons'][2]."'></td>";
      echo "</td></tr>";
      echo "</table></form></div>";

   }
   function defineTabs($options=array()) {
      global $LANG;

      $ong = array();
      $ong[1] = $LANG['title'][26];

      if ($this->fields['id'] > 0) {
         $ong[2] = $LANG['Menu'][14];
         $ong[3] = $LANG['Menu'][36];
         $ong[4] = $LANG['entity'][0];
         $ong[5] = $LANG['entity'][14];
         $ong[6] = $LANG['ldap'][22];
      }
      return $ong;
   }

   function getSearchOptions() {
      global $LANG;

      $tab = array();
      $tab['common'] = $LANG['login'][2];

      $tab[1]['table']         = 'glpi_authldaps';
      $tab[1]['field']         = 'name';
      $tab[1]['linkfield']     = 'name';
      $tab[1]['name']          = $LANG['common'][16];
      $tab[1]['datatype']      = 'itemlink';
      $tab[1]['itemlink_type'] = 'AuthLDAP';

      $tab[2]['table']        = 'glpi_authldaps';
      $tab[2]['field']        = 'id';
      $tab[2]['linkfield']    = '';
      $tab[2]['name']         = $LANG['common'][2];

      $tab[3]['table']         = 'glpi_authldaps';
      $tab[3]['field']         = 'host';
      $tab[3]['linkfield']     = 'host';
      $tab[3]['name']          = $LANG['common'][52];

      $tab[4]['table']         = 'glpi_authldaps';
      $tab[4]['field']         = 'port';
      $tab[4]['linkfield']     = 'port';
      $tab[4]['name']          = $LANG['setup'][175];

      $tab[5]['table']         = 'glpi_authldaps';
      $tab[5]['field']         = 'basedn';
      $tab[5]['linkfield']     = 'basedn';
      $tab[5]['name']          = $LANG['setup'][154];

      $tab[6]['table']         = 'glpi_authldaps';
      $tab[6]['field']         = 'condition';
      $tab[6]['linkfield']     = 'condition';
      $tab[6]['name']          = $LANG['setup'][159];

      $tab[19]['table']       = 'glpi_authldaps';
      $tab[19]['field']       = 'date_mod';
      $tab[19]['linkfield']   = '';
      $tab[19]['name']        = $LANG['common'][26];
      $tab[19]['datatype']    = 'datetime';

      $tab[16]['table']     = 'glpi_authldaps';
      $tab[16]['field']     = 'comment';
      $tab[16]['linkfield'] = 'comment';
      $tab[16]['name']      = $LANG['common'][25];
      $tab[16]['datatype']  = 'text';

      return $tab;
   }

   function showSystemInformations($width) {
      global $LANG;

      $ldap_servers = AuthLdap::getLdapServers ();

      if (!empty($ldap_servers)) {
         echo "\n</pre></td><tr class='tab_bg_2'><th>" . $LANG['login'][2] . "</th></tr>\n";
         echo "<tr class='tab_bg_1'><td><pre>\n&nbsp;\n";
         foreach ($ldap_servers as $ID => $value) {
            $fields = array($LANG['common'][52]=>'host',
                            $LANG['setup'][172]=>'port',
                            $LANG['setup'][154]=>'basedn',
                            $LANG['setup'][159]=>'condition',
                            $LANG['setup'][155]=>'rootdn',
                            $LANG['setup'][180]=>'use_tls');
            $msg = '';
            $first = true;
            foreach($fields as $label => $field) {
               $msg .= (!$first?', ':'').$label.': '.($value[$field] != ''?'\''.$value[$field].
                        '\'':$LANG['common'][49]);
               $first = false;
            }
            echo wordwrap($msg."\n", $width, "\n\t\t");
         }
      }

      echo "\n</pre></td></tr>";
   }

   /**
    * Get LDAP fields to sync to GLPI data from a glpi_authldaps array
    *
    * @param $authtype_array Authentication method config array (from table)
    *
    * @return array of "user table field name" => "config value"
    */
   static function getSyncFields($authtype_array) {

      $ret = array();

      $fields = array('login_field'     => 'name',
                      'email_field'     => 'email',
                      'realname_field'  => 'realname',
                      'firstname_field' => 'firstname',
                      'phone_field'     => 'phone',
                      'phone2_field'    => 'phone2',
                      'mobile_field'    => 'mobile',
                      'comment_field'   => 'comment',
                      'title_field'     => 'usertitles_id',
                      'category_field'  => 'usercategories_id',
                      'language_field'  => 'language');

      foreach ($fields as $key => $val) {
         if (isset($authtype_array[$key])) {
            $ret[$val] = $authtype_array[$key];
         }
      }
      return $ret;
   }

   /** Display LDAP filter
    *
    * @param   $target target for the form
    * @param   $users boolean : for user ?
    * @return nothing
    */
   static function displayLdapFilter($target,$users=true) {
      global $LANG;

      $config_ldap = new AuthLDAP();
      $res = $config_ldap->getFromDB($_SESSION["ldap_server"]);

      if ($users) {
         $filter_name1="condition";
         $filter_var = "ldap_filter";
      } else {
         $filter_var = "ldap_group_filter";
         switch ($config_ldap->fields["group_search_type"]) {
            case 0 :
               $filter_name1="condition";
               break;

            case 1 :
               $filter_name1="group_condition";
               break;

            case 2:
               $filter_name1="group_condition";
               $filter_name2="condition";
               break;
         }
      }

      if (!isset($_SESSION[$filter_var]) || $_SESSION[$filter_var] == '') {
         $_SESSION[$filter_var]=$config_ldap->fields[$filter_name1];
      }

      echo "<div class='center'>";
      echo "<form method='post' action=\"$target\">";
      echo "<table class='tab_cadre'>";
      echo "<tr><th colspan='2'>" . ($users?$LANG['setup'][263]:$LANG['setup'][253]) . "</th></tr>";
      echo "<tr class='tab_bg_2'><td>";
      echo "<input type='text' name='ldap_filter' value='" . $_SESSION[$filter_var] . "' size='70'>";

      //Only display when looking for groups in users AND groups
      if (!$users && $config_ldap->fields["group_search_type"] == 2) {
         if (!isset($_SESSION["ldap_group_filter2"]) || $_SESSION["ldap_group_filter2"] == '') {
            $_SESSION["ldap_group_filter2"]=$config_ldap->fields[$filter_name2];
         }

         echo "</td></tr>";
         echo "<tr><th colspan='2'>" . $LANG['setup'][263] . "</th></tr>";
         echo "<tr class='tab_bg_2'><td>";
         echo "<input type='text' name='ldap_filter2' value='" .
                $_SESSION["ldap_group_filter2"] . "' size='70'>";
         echo "</td></tr>";
      }

      echo "<tr class='tab_bg_2'><td class='center'>";
      echo "<input class=submit type='submit' name='change_ldap_filter' value='" .
             $LANG['buttons'][2] . "'>";
      echo "</td></tr></table>";
      echo "</form></div>";
   }

   /** Converts LDAP timestamps over to Unix timestamps
    *
    * @param   $ldapstamp LDAP timestamp
    * @param   $ldap_time_offset time offset
    * @return unix timestamp
    */
   static function ldapStamp2UnixStamp($ldapstamp,$ldap_time_offset=0) {
      global $CFG_GLPI;

      $year=substr($ldapstamp,0,4);
      $month=substr($ldapstamp,4,2);
      $day=substr($ldapstamp,6,2);
      $hour=substr($ldapstamp,8,2);
      $minute=substr($ldapstamp,10,2);
      $seconds=substr($ldapstamp,12,2);
      $stamp=gmmktime($hour,$minute,$seconds,$month,$day,$year);
      $stamp+= $CFG_GLPI["time_offset"]-$ldap_time_offset;

      return $stamp;
   }

   /** Form part to change mail auth method of a user
    *
    * @param   $ID ID of the user
    * @return nothing
    */
   static function formChangeAuthMethodToMail($ID) {
      global $LANG,$DB;

      $sql = "SELECT `id`
              FROM `glpi_authmails`";
      $result = $DB->query($sql);
      if ($DB->numrows($result) > 0) {
         echo "<table class='tab_cadre'>";
         echo "<tr><th colspan='2' colspan='2'>" . $LANG['login'][30]." : ".$LANG['login'][3]."</th></tr>";
         echo "<tr class='tab_bg_1'><td><input type='hidden' name='id' value='" . $ID . "'>";
         echo $LANG['login'][33]."</td><td>";
         Dropdown::show('AuthMail', array('name' => "auths_id"));
         echo "</td>";
         echo "<tr class='tab_bg_2'><td colspan='2'class='center'>";
         echo "<input class=submit type='submit' name='switch_auth_mail' value='" .
                $LANG['buttons'][2] . "'>";
         echo "</td></tr></table>";
      }
   }

   /** Form part to change ldap auth method of a user
    *
    * @param   $ID ID of the user
    * @return nothing
    */
   static function formChangeAuthMethodToLDAP($ID) {
      global $LANG,$DB;

      $sql = "SELECT `id`
              FROM `glpi_authldaps`";
      $result = $DB->query($sql);
      if ($DB->numrows($result) > 0) {
         echo "<table class='tab_cadre'>";
         echo "<tr><th colspan='2' colspan='2'>" . $LANG['login'][30]." : ".$LANG['login'][2]."</th></tr>";
         echo "<tr class='tab_bg_1'><td><input type='hidden' name='id' value='" . $ID . "'>";
         echo $LANG['login'][31]."</td><td>";
         Dropdown::show('AuthLDAP', array('name' => "auths_id"));
         echo "</td>";
         echo "<tr class='tab_bg_2'><td colspan='2'class='center'>";
         echo "<input class=submit type='submit' name='switch_auth_ldap' value='" .
                $LANG['buttons'][2] . "'>";
         echo "</td></tr></table>";
      }
   }

   /** Form part to change auth method of a user
    *
    * @param   $ID ID of the user
    * @return nothing
    */
   static function formChangeAuthMethodToDB($ID) {
      global $LANG;

      echo "<br><table class='tab_cadre'>";
      echo "<tr><th colspan='2' colspan='2'>" . $LANG['login'][30]."</th></tr>";
      echo "<input type='hidden' name='id' value='" . $ID . "'>";
      echo "<tr class='tab_bg_2'><td colspan='2'class='center'>";
      echo "<input class=submit type='submit' name='switch_auth_internal' value='" .
             $LANG['login'][32] . "'>";
      echo "</td></tr></table>";
   }

   /** Display refresh button in the user page
    *
    * @param   $target target for the form
    * @param   $ID ID of the user
    * @return nothing
    */
   static function showSynchronizationForm($target, $ID) {
      global $LANG, $DB, $CFG_GLPI;

      if (haveRight("user", "w")) {
         //Look it the user's auth method is LDAP
         $sql = "SELECT `authtype`, `auths_id`
                 FROM `glpi_users`
                 WHERE `id`='" . $ID."'";
         $result = $DB->query($sql);

         if ($DB->numrows($result) == 1) {
            $data = $DB->fetch_array($result);
            echo "<div class='center'>";
            echo "<form method='post' action=\"$target\">";
            switch($data["authtype"]) {
               case AUTH_LDAP :
                  //Look it the auth server still exists !
                  // <- Bad idea : id not exists unable to change anything
                  $sql = "SELECT `name`
                          FROM `glpi_authldaps`
                          WHERE `id`='" . $data["auths_id"]."'";
                  $result = $DB->query($sql);
                  if ($DB->numrows($result) > 0) {
                     echo "<table class='tab_cadre'><tr class='tab_bg_2'><td>";
                     echo "<input type='hidden' name='id' value='" . $ID . "'>";
                     echo "<input class=submit type='submit' name='force_ldap_resynch' value='" .
                            $LANG['ocsng'][24] . "'>";
                     echo "</td></tr></table>";
                  }
                  AuthLdap::formChangeAuthMethodToDB($ID);
                  echo "<br>";
                  AuthLdap::formChangeAuthMethodToMail($ID);
                  break;

               case AUTH_DB_GLPI :
                  AuthLdap::formChangeAuthMethodToLDAP($ID);
                  echo "<br>";
                  AuthLdap::formChangeAuthMethodToMail($ID);
                  break;

               case AUTH_MAIL :
                  AuthLdap::formChangeAuthMethodToDB($ID);
                  echo "<br>";
                  AuthLdap::formChangeAuthMethodToLDAP($ID);
                  break;

               case AUTH_CAS :
               case AUTH_EXTERNAL :
               case AUTH_X509 :
                  if ($CFG_GLPI['authldaps_id_extra']) {
                     $sql = "SELECT `name`
                             FROM `glpi_authldaps`
                             WHERE `id`='" .$CFG_GLPI['authldaps_id_extra']."'";
                     $result = $DB->query($sql);

                     if ($DB->numrows($result) > 0) {
                        echo "<table class='tab_cadre'><tr class='tab_bg_2'><td>";
                        echo "<input type='hidden' name='id' value='" . $ID . "'>";
                        echo "<input class=submit type='submit' name='force_ldap_resynch' value='" .
                               $LANG['ocsng'][24] . "'>";
                        echo "</td></tr></table>";
                     }
                     echo "<br>";
                  }
                  AuthLdap::formChangeAuthMethodToDB($ID);
                  echo "<br>";
                  AuthLdap::formChangeAuthMethodToLDAP($ID);
                  echo "<br>";
                  AuthLdap::formChangeAuthMethodToMail($ID);
                  break;
            }
            echo "</form></div>";
         }
      }
   }

   /** Test a LDAP connection
    *
    * @param   $auths_id ID of the LDAP server
    * @param   $replicate_id use a replicate if > 0
    * @return  boolean connection succeeded ?
    */
   static function testLDAPConnection($auths_id,$replicate_id=-1) {

      $config_ldap = new AuthLDAP();
      $res = $config_ldap->getFromDB($auths_id);
      $ldap_users = array ();

      // we prevent some delay...
      if (!$res) {
         return false;
      }

      //Test connection to a replicate
      if ($replicate_id != -1) {
         $replicate = new AuthLdapReplicate;
         $replicate->getFromDB($replicate_id);
         $host = $replicate->fields["host"];
         $port = $replicate->fields["port"];
      } else {
         //Test connection to a master ldap server
         $host = $config_ldap->fields['host'];
         $port = $config_ldap->fields['port'];
      }
      $ds = AuthLdap::connectToServer($host, $port, $config_ldap->fields['rootdn'],
                         $config_ldap->fields['rootdn_password'], $config_ldap->fields['use_tls'],
                         $config_ldap->fields['deref_option']);
      if ($ds) {
         return true;
      } else {
         return false;
      }
   }

   /** Show LDAP users to add or synchronise
    *
    * @param   $target target page for the form
    * @param   $check check all ? -> need to be delete
    * @param   $start where to start the list
    * @param   $sync synchronise or add ?
    * @param   $filter ldap filter to use
    * @param   $order display order
    * @return  nothing
    */
   static function showLdapUsers($target, $check, $start, $sync = 0,$filter='',$order='DESC') {
      global $DB, $CFG_GLPI, $LANG;

      AuthLdap::displayLdapFilter($target);
      echo "<br>";
      $ldap_users = AuthLdap::getAllLdapUsers($_SESSION["ldap_server"], $sync,$filter,$order);

      if (is_array($ldap_users)) {
         $numrows = count($ldap_users);
         if (!$sync) {
            $action = "toimport";
            $form_action = "import_ok";
         } else {
            $action = "tosync";
            $form_action = "sync_ok";
         }

         if ($numrows > 0) {
            $parameters = "check=$check";
            printPager($start, $numrows, $target, $parameters);

            // delete end
            array_splice($ldap_users, $start + $_SESSION['glpilist_limit']);
            // delete begin
            if ($start > 0) {
               array_splice($ldap_users, 0, $start);
            }

            echo "<div class='center'>";
            echo "<form method='post' id='ldap_form' name='ldap_form' action='" . $target . "'>";
            echo "<a href='" .
                  $target . "?check=all' onclick= \"if ( markCheckboxes('ldap_form') ) return false;\">" .
                  $LANG['buttons'][18] . "</a>&nbsp;/&nbsp;<a href='" .
                  $target . "?check=none' onclick= \"if ( unMarkCheckboxes('ldap_form') ) return false;\">" .
                  $LANG['buttons'][19] . "</a>";
            echo "<table class='tab_cadre'>";
            echo "<tr><th>" . (!$sync?$LANG['buttons'][37]:$LANG['ldap'][15]) . "</th>";
            $num=0;
            echo displaySearchHeaderItem(0,$LANG['Menu'][14],$num,$target.
                                         "?order=".($order=="DESC"?"ASC":"DESC"),1,$order);
            echo "<th>".$LANG['common'][26]." ".$LANG['ldap'][13]."</th>";
            echo "<th>".$LANG['common'][26]." ".$LANG['ldap'][14]."</th>";
            echo "</tr>";

            foreach ($ldap_users as $userinfos) {
               $user = $userinfos["user"];
               if (isset($userinfos["timestamp"])) {
                  $stamp = $userinfos["timestamp"];
               } else {
                  $stamp='';
               }

               if (isset($userinfos["date_mod"])) {
                  $date_mod = $userinfos["date_mod"];
               } else {
                  $date_mod='';
               }

               echo "<tr class='tab_bg_2 center'>";
               //Need to use " instead of ' because it doesn't work with names with ' inside !
               echo "<td><input type='checkbox' name=\"" . $action . "[" . $user . "]\" " .
                           ($check == "all" ? "checked" : "") ."></td>";
               echo "<td>" . $user . "</td>";

               if ($stamp != '') {
                  echo "<td>" .convDateTime(date("Y-m-d H:i:s",$stamp)). "</td>";
               } else {
                  echo "<td>&nbsp;</td>";
               }
               if ($date_mod != '') {
                  echo "<td>" . convDateTime($date_mod) . "</td>";
               } else {
                  echo "<td>&nbsp;</td>";
               }
               echo "</tr>";
            }
            echo "<tr class='tab_bg_1'><td colspan='5' class='center'>";
            echo "<input class='submit' type='submit' name='" . $form_action . "' value='" .
                   (!$sync?$LANG['buttons'][37]:$LANG['ldap'][15]) . "'>";
            echo "</td></tr>";
            echo "</table></form></div>";
            echo "<a href='" .
                  $target . "?check=all' onclick= \"if ( markCheckboxes('ldap_form') ) return false;\">" .
                  $LANG['buttons'][18] . "</a>&nbsp;/&nbsp;<a href='" .
                  $target . "?check=none' onclick= \"if ( unMarkCheckboxes('ldap_form') ) return false;\">" .
                  $LANG['buttons'][19] . "</a>";
            printPager($start, $numrows, $target, $parameters);
         } else {
            echo "<div class='center'><strong>" . $LANG['ldap'][3] . "</strong></div>";
         }
      } else {
         echo "<div class='center'><strong>" . $LANG['ldap'][3] . "</strong></div>";
      }
   }

   /** Get the list of LDAP users to add/synchronize
    *
    * @param   $auths_id ID of the server to use
    * @param   $sync user to synchronise or add ?
    * @param   $myfilter ldap filter to use
    * @param   $order display order
    * @return  array of the user
    */
   static function getAllLdapUsers($auths_id, $sync = 0,$myfilter='',$order='DESC') {
      global $DB, $LANG,$CFG_GLPI;

      $config_ldap = new AuthLDAP();
      $res = $config_ldap->getFromDB($auths_id);
      $ldap_users = array ();

      // we prevent some delay...
      if (!$res) {
         return false;
      }
      if ($order!="DESC") {
         $order="ASC";
      }
      $ds = AuthLdap::connectToServer($config_ldap->fields['host'], $config_ldap->fields['port'],
                         $config_ldap->fields['rootdn'], $config_ldap->fields['rootdn_password'],
                         $config_ldap->fields['use_tls'], $config_ldap->fields['deref_option']);
      if ($ds) {
         //Search for ldap login AND modifyTimestamp,
         //which indicates the last update of the object in directory
         $attrs = array ($config_ldap->fields['login_field'],
                         "modifyTimestamp");

         // Tenter une recherche pour essayer de retrouver le DN
         if ($myfilter == '') {
            $filter = "(".$config_ldap->fields['login_field']."=*)";
         } else {
            $filter = $myfilter;
         }

         if (!empty ($config_ldap->fields['condition'])) {
            $filter = "(& $filter ".$config_ldap->fields['condition'].")";
         }

         $sr = @ldap_search($ds, $config_ldap->fields['basedn'],$filter , $attrs);
         if ($sr) {
            $info = ldap_get_entries($ds, $sr);
            $user_infos = array();

            for ($ligne = 0; $ligne < $info["count"]; $ligne++) {
               //If ldap add
               if (!$sync) {
                  if (in_array($config_ldap->fields['login_field'],$info[$ligne])) {
                     $ldap_users[$info[$ligne][$config_ldap->fields['login_field']][0]] =
                        $info[$ligne][$config_ldap->fields['login_field']][0];
                     $user_infos[$info[$ligne][$config_ldap->fields['login_field']][0]]["timestamp"]=
                        AuthLdap::ldapStamp2UnixStamp($info[$ligne]['modifytimestamp'][0],
                                            $config_ldap->fields['time_offset']);
                  }
               } else {
                  //If ldap synchronisation
                  if (in_array($config_ldap->fields['login_field'],$info[$ligne])) {
                     $ldap_users[$info[$ligne][$config_ldap->fields['login_field']][0]] =
                        AuthLdap::ldapStamp2UnixStamp($info[$ligne]['modifytimestamp'][0],
                                            $config_ldap->fields['time_offset']);
                     $user_infos[$info[$ligne][$config_ldap->fields['login_field']][0]]["timestamp"]=
                        AuthLdap::ldapStamp2UnixStamp($info[$ligne]['modifytimestamp'][0],
                                            $config_ldap->fields['time_offset']);
                   }
               }
            }
         } else {
            return false;
         }
      } else {
         return false;
      }

      $glpi_users = array ();
      $sql = "SELECT `name`, `date_mod`
              FROM `glpi_users` ";
      if ($sync) {
         $sql.=" WHERE `authtype` IN (-1,".AUTH_LDAP.",".AUTH_EXTERNAL.") ";
      }
      $sql.="ORDER BY `name` ".$order;

      $result = $DB->query($sql);
      if ($DB->numrows($result) > 0) {
         while ($user = $DB->fetch_array($result)) {
            //Ldap add : fill the array with the login of the user
            if (!$sync) {
               $glpi_users[$user['name']] = $user['name'];
            } else {
               //Ldap synchronisation : look if the user exists in the directory
               //and compares the modifications dates (ldap and glpi db)
               if (!empty ($ldap_users[$user['name']])) {
                  //If entry was modified or if script should synchronize all the users
                  if ( ($sync ==2) || ($ldap_users[$user['name']] - strtotime($user['date_mod']) > 0)) {
                     $glpi_users[] = array("user" => $user['name'],
                                           "timestamp"=>$user_infos[$user['name']]['timestamp'],
                                           "date_mod"=>$user['date_mod']);
                  }
               }
            }
         }
      }
      //If add, do the difference between ldap users and glpi users
      if (!$sync) {
         $diff = array_diff_ukey($ldap_users,$glpi_users,'strcasecmp');
         $list = array();

         foreach ($diff as $user) {
            $list[] = array("user" => $user,
                            "timestamp" => $user_infos[$user]["timestamp"],
                            "date_mod"=> "-----");
         }
         if ($order=='DESC') {
            rsort($list);
         } else {
            sort($list);
         }
         return $list;
      } else {
         return $glpi_users;
      }
   }

   /** Show LDAP groups to add or synchronise in an entity
    *
    * @param   $target target page for the form
    * @param   $check check all ? -> need to be delete
    * @param   $start where to start the list
    * @param   $sync synchronise or add ?
    * @param   $filter ldap filter to use
    * @param   $filter2 second ldap filter to use (which case ?)
    * @param   $entity working entity
    * @param   $order display order
    * @return  nothing
    */
   static function showLdapGroups($target, $check, $start, $sync = 0,$filter='',$filter2='',
                           $entity,$order='DESC') {
      global $DB, $CFG_GLPI, $LANG;

      AuthLdap::displayLdapFilter($target,false);
      echo "<br>";
      $ldap_groups = AuthLdap::getAllGroups($_SESSION["ldap_server"],$filter,$filter2,$entity,$order);

      if (is_array($ldap_groups)) {
         $numrows = count($ldap_groups);
         $action = "toimport";
         $form_action = "import_ok";

         if ($numrows > 0) {
            $parameters = "check=$check";
            printPager($start, $numrows, $target, $parameters);

            // delete end
            array_splice($ldap_groups, $start + $_SESSION['glpilist_limit']);
            // delete begin
            if ($start > 0) {
               array_splice($ldap_groups, 0, $start);
            }

            echo "<div class='center'>";
            echo "<form method='post' id='ldap_form' name='ldap_form'  action='" . $target . "'>";
            echo "<a href='" .
                  $target . "?check=all' onclick= \"if ( markCheckboxes('ldap_form') ) return false;\">" .
                  $LANG['buttons'][18] . "</a>&nbsp;/&nbsp;<a href='" .
                  $target . "?check=none' onclick= \"if ( unMarkCheckboxes('ldap_form') ) return false;\">" .
                  $LANG['buttons'][19] . "</a>";
            echo "<table class='tab_cadre'>";
            echo "<tr><th>" . $LANG['buttons'][37]. "</th>";
            $header_num=0;
            echo displaySearchHeaderItem(HTML_OUTPUT,$LANG['common'][35],$header_num,$target.
                                         "?order=".($order=="DESC"?"ASC":"DESC"),1,$order);
            echo "<th>".$LANG['setup'][261]."</th>";
            echo"<th>".$LANG['ocsng'][36]."</th></tr>";

            foreach ($ldap_groups as $groupinfos) {
               $group = $groupinfos["cn"];
               $group_dn = $groupinfos["dn"];
               $search_type = $groupinfos["search_type"];

               echo "<tr class='tab_bg_2 center'>";
               //Need to use " instead of ' because it doesn't work with names with ' inside !
               echo "<td><input type='checkbox' name=\"" . $action . "[" .$group_dn . "]\" " .
                            ($check == "all" ? "checked" : "") ."></td>";
               echo "<td>" . $group . "</td>";
               echo "<td>" .$group_dn. "</td>";
               echo "<td>";
               Dropdown::show('Entity',
                              array('value'  => $entity,
                                    'name'   => "toimport_entities[" .$group_dn . "]=".$entity));
               echo "</td>";
               echo "<input type='hidden' name=\"toimport_type[".$group_dn."]\" value=\"".
                        $search_type."\"></tr>";
            }
            echo "<tr class='tab_bg_1'><td colspan='4' class='center'>";
            echo "<input class='submit' type='submit' name='" . $form_action . "' value='" .
                   $LANG['buttons'][37] . "'>";
            echo "</td></tr>";
            echo "</table></form></div>";
            printPager($start, $numrows, $target, $parameters);
         } else {
            echo "<div class='center'><strong>" . $LANG['ldap'][25] . "</strong></div>";
         }
      } else {
         echo "<div class='center'><strong>" . $LANG['ldap'][25] . "</strong></div>";
      }
   }

   /** Get all LDAP groups from a ldap server which are not already in an entity
    *
    * @param   $auths_id ID of the server to use
    * @param   $filter ldap filter to use
    * @param   $filter2 second ldap filter to use if needed
    * @param   $entity entity to search
    * @param   $order order to use
    * @return  array of the groups
    */
   static function getAllGroups($auths_id,$filter,$filter2,$entity,$order='DESC') {
      global $DB, $LANG,$CFG_GLPI;

      $config_ldap = new AuthLDAP();
      $res = $config_ldap->getFromDB($auths_id);
      $infos = array();
      $groups = array();

      $ds = AuthLdap::connectToServer($config_ldap->fields['host'], $config_ldap->fields['port'],
                         $config_ldap->fields['rootdn'], $config_ldap->fields['rootdn_password'],
                         $config_ldap->fields['use_tls'], $config_ldap->fields['deref_option']);
      if ($ds) {
         switch ($config_ldap->fields["group_search_type"]) {
            case 0 :
               $infos = AuthLdap::getGroupsFromLDAP($ds,$config_ldap,$filter,false,$infos);
               break;

            case 1 :
               $infos = AuthLdap::getGroupsFromLDAP($ds,$config_ldap,$filter,true,$infos);
               break;

            case 2 :
               $infos = AuthLdap::getGroupsFromLDAP($ds,$config_ldap,$filter,true,$infos);
               $infos = AuthLdap::getGroupsFromLDAP($ds,$config_ldap,$filter2,false,$infos);
               break;
         }

         if (!empty($infos)) {
            $glpi_groups = array();
            //Get all groups from GLPI DB for the current entity and the subentities
            $sql = "SELECT `name`
                    FROM `glpi_groups` ".
                    getEntitiesRestrictRequest("WHERE","glpi_groups");

            $res = $DB->query($sql);
            //If the group exists in DB -> unset it from the LDAP groups
            while ($group = $DB->fetch_array($res)) {
               $glpi_groups[$group["name"]] = 1;
            }
            $ligne=0;

            foreach ($infos as $dn => $info) {
               if (!isset($glpi_groups[$info["cn"]])) {
                  $groups[$ligne]["dn"]=$dn;
                  $groups[$ligne]["cn"]=$info["cn"];
                  $groups[$ligne]["search_type"]=$info["search_type"];
                  $ligne++;
               }
            }
         }



         if ($order=='DESC') {
            function local_cmp($b,$a) {
               return strcasecmp($a['cn'],$b['cn']);
            }
         } else {
            function local_cmp($a,$b) {
               return strcasecmp($a['cn'],$b['cn']);
            }
         }
         usort($groups,'local_cmp');

      }
      return $groups;
   }

   /**
    * Get the group's cn by giving his DN
    * @param $ldap_connection ldap connection to use
    * @param $group_dn the group's dn
    * @return the group cn
    */
   static function getGroupCNByDn($ldap_connection,$group_dn) {

      $sr = @ ldap_read($ldap_connection, $group_dn, "objectClass=*", array("cn"));
      $v = ldap_get_entries($ldap_connection, $sr);
      if (!is_array($v) || count($v) == 0 || empty ($v[0]["cn"][0])) {
         return false;
      } else {
         return $v[0]["cn"][0];
      }
   }

   static function getGroupsFromLDAP($ldap_connection,$config_ldap,$filter,$search_in_groups=true,
                              $groups=array()) {

      //First look for groups in group objects
      $extra_attribute = ($search_in_groups?"cn":$config_ldap->fields["group_field"]);
      $attrs = array ("dn",
                      $extra_attribute);

      if ($filter == '') {
         if ($search_in_groups) {
            $filter = (!empty($config_ldap->fields['group_condition'])?
                       $config_ldap->fields['group_condition']:"(objectclass=*)");
         } else {
            $filter = (!empty($config_ldap->fields['condition'])?
                       $config_ldap->fields['condition']:"(objectclass=*)");
         }
         $sr = @ldap_search($ldap_connection, $config_ldap->fields['basedn'],$filter , $attrs);

         if ($sr) {
            $infos = ldap_get_entries($ldap_connection, $sr);
            for ($ligne=0; $ligne < $infos["count"];$ligne++) {
               if ($search_in_groups) {
                  // No cn : not a real object
                  if (isset($infos[$ligne]["cn"][0])) {
                     $cn = $infos[$ligne]["cn"][0];
                     $groups[$infos[$ligne]["dn"]]= (array("cn"=>$infos[$ligne]["cn"][0],
                                                           "search_type" => "groups"));
                  }
               } else {
                  if (isset($infos[$ligne][$extra_attribute])) {
                     for ($ligne_extra=0; $ligne_extra < $infos[$ligne][$extra_attribute]["count"];
                          $ligne_extra++) {

                        $groups[$infos[$ligne][$extra_attribute][$ligne_extra]]=
                           array("cn"=>AuthLdap::getGroupCNByDn($ldap_connection,
                                                      $infos[$ligne][$extra_attribute][$ligne_extra]),
                                 "search_type" => "users");
                     }
                  }
               }
            }
         }
      }
      return $groups;
   }

   /** Form to choose a ldap server
    *
    * @param   $target target page for the form
    * @return  nothing
    */
   static function ldapChooseDirectory($target) {
      global $DB, $LANG;

      $query = "SELECT *
                FROM `glpi_authldaps`
                ORDER BY `name` ASC";
      $result = $DB->query($query);

      if ($DB->numrows($result) == 1) {
         //If only one server, do not show the choose ldap server window
         $ldap = $DB->fetch_array($result);
         $_SESSION["ldap_server"]=$ldap["id"];
         glpi_header($_SERVER['PHP_SELF']);
      }

      echo "<form action=\"$target\" method=\"post\">";
      echo "<div class='center'>";
      echo "<p >" . $LANG['ldap'][5] . "</p>";
      echo "<table class='tab_cadre'>";
      echo "<tr class='tab_bg_2'><th colspan='2'>" . $LANG['ldap'][4] . "</th></tr>";
      //If more than one ldap server
      if ($DB->numrows($result) > 1) {
         echo "<tr class='tab_bg_2'><td class='center'>" . $LANG['common'][16] . "</td>";
         echo "<td class='center'>";
         echo "<select name='ldap_server'>";
         while ($ldap = $DB->fetch_array($result)) {
            echo "<option value=" . $ldap["id"] . ">" . $ldap["name"] . "</option>";
         }
         echo "</select></td></tr>";
         echo "<tr class='tab_bg_2'><td class='center' colspan='2'>";
         echo "<input class='submit' type='submit' name='ldap_showusers' value='" .
                $LANG['buttons'][2] . "'></td></tr>";
      } else {
         //No ldap server
         echo "<tr class='tab_bg_2'><td class='center' colspan='2'>" . $LANG['ldap'][7] . "</td></tr>";
      }
      echo "</table></div></form>";
   }

   /** Import a user from a specific ldap server
    *
    * @param   $login  dn of the user to import
    * @param   $sync synchoronise (true) or import (false)
    * @param   $ldap_server ID of the LDAP server to use
    * @param   $display display message information on redirect
    * @return  nothing
    */
   static function ldapImportUserByServerId($login, $sync,$ldap_server,$display=false) {
      global $DB, $LANG;

      $config_ldap = new AuthLDAP();
      $res = $config_ldap->getFromDB($ldap_server);
      $ldap_users = array ();

      // we prevent some delay...
      if (!$res) {
         return false;
      }

      //Connect to the directory
      $ds = AuthLdap::connectToServer($config_ldap->fields['host'], $config_ldap->fields['port'],
                         $config_ldap->fields['rootdn'], $config_ldap->fields['rootdn_password'],
                         $config_ldap->fields['use_tls'],$config_ldap->fields['deref_option']);
      if ($ds) {
         //Get the user's dn
         $user_dn = AuthLdap::searchUserDn($ds, $config_ldap->fields['basedn'],
                                        $config_ldap->fields['login_field'], stripslashes($login),
                                        $config_ldap->fields['condition']);
         if ($user_dn) {
            $rule = new RuleRightCollection;
            $groups = array();
            $user = new User();
            //Get informations from LDAP
            if ($user->getFromLDAP($ds, $config_ldap->fields, $user_dn, addslashes($login), "")) {
               //Add the auth method
               if (!$sync) {
                  $user->fields["authtype"] = AUTH_LDAP;
                  $user->fields["auths_id"] = $ldap_server;
               }
               // Force date mod
               $user->fields["date_mod"]=$_SESSION["glpi_currenttime"];

               if (!$sync) {
                  //Save informations in database !
                  $input = $user->fields;
                  unset ($user->fields);
                  // Display message after redirect
                  if ($display) {
                     $input['add']=1;
                  }
                  $user->fields["id"] = $user->add($input);
                  return $user->fields["id"];
               } else {
                  $input=$user->fields;
                  if ($display) {
                     $input['update']=1;
                  }
                  $user->update($input);
                  return true;
               }
            } else {
               return false;
            }
         }
      } else {
         return false;
      }
   }

   /** Import a user from the active ldap server
    *
    * @param   $login  dn of the user to import
    * @param   $sync synchoronise (true) or import (false)
    * @param   $display display message information on redirect
    * @return  nothing
    */
   static function ldapImportUser ($login,$sync,$display=false) {
      AuthLdap::ldapImportUserByServerId($login, $sync,$_SESSION["ldap_server"],$display);
   }

   /** Converts an array of parameters into a query string to be appended to a URL.
    *
    * @param   $group_dn  dn of the group to import
    * @param   $ldap_server ID of the LDAP server to use
    * @param   $entity entity where group must to be imported
    * @param   $type the type of import (groups, users, users & groups)
    * @return  nothing
    */
   static function ldapImportGroup ($group_dn,$ldap_server,$entity,$type) {

      $config_ldap = new AuthLDAP();
      $res = $config_ldap->getFromDB($ldap_server);
      $ldap_users = array ();
      $group_dn = $group_dn;

      // we prevent some delay...
      if (!$res) {
         return false;
      }

      //Connect to the directory
      $ds = AuthLdap::connectToServer($config_ldap->fields['host'], $config_ldap->fields['port'],
                         $config_ldap->fields['rootdn'], $config_ldap->fields['rootdn_password'],
                         $config_ldap->fields['use_tls'],$config_ldap->fields['deref_option']);
      if ($ds) {
         $group_infos = AuthLdap::getGroupByDn($ds, $config_ldap->fields['basedn'],
                           stripslashes($group_dn),$config_ldap->fields["group_condition"]);
         $group = new Group();
         if ($type == "groups") {
            $group->add(array("name"=>addslashes($group_infos["cn"][0]),
                              "ldap_group_dn"=>addslashes($group_infos["dn"]),
                              "entities_id"=>$entity));
         } else {
            $group->add(array("name"=>addslashes($group_infos["cn"][0]),
                              "ldap_field"=>$config_ldap->fields["group_field"],
                              "ldap_value"=>addslashes($group_infos["dn"]),
                              "entities_id"=>$entity));
         }
      }
   }

   /**
    * Connect to a LDAP serveur
    *
    * @param $host : LDAP host to connect
    * @param $port : port to use
    * @param $login : login to use
    * @param $password : password to use
    * @param $use_tls : use a tls connection ?
    * @param $deref_options Deref options used
    * @return link to the LDAP server : false if connection failed
   **/
   static function connectToServer($host, $port, $login = "", $password = "", $use_tls = false,$deref_options) {
      global $CFG_GLPI;

      $ds = @ldap_connect($host, intval($port));
      if ($ds) {
         @ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
         @ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
         @ldap_set_option($ds, LDAP_OPT_DEREF, $deref_options);
         if ($use_tls) {
            if (!@ldap_start_tls($ds)) {
               return false;
            }
         }
         // Auth bind
         if ($login != '') {
            $b = @ldap_bind($ds, $login, $password);
         } else { // Anonymous bind
            $b = @ldap_bind($ds);
         }
         if ($b) {
            return $ds;
         } else {
            return false;
         }
      } else {
         return false;
      }
   }

   /**
    * Try to connect to a ldap server
    *
    * @param $ldap_method : ldap_method array to use
    * @param $login User Login
    * @param $password User Password

    * @return link to the LDAP server : false if connection failed
   **/
   static function tryToConnectToServer($ldap_method,$login, $password){

      $ds = AuthLdap::connectToServer($ldap_method['host'], $ldap_method['port'],
                           $ldap_method['rootdn'], $ldap_method['rootdn_password'],
                           $ldap_method['use_tls'], $ldap_method['deref_option']);
      // Test with login and password of the user if exists
      if (!$ds && !empty($login)) {
         $ds = AuthLdap::connectToServer($ldap_method['host'], $ldap_method['port'],
                              $login, $password,
                              $ldap_method['use_tls'], $ldap_method['deref_option']);
      }

      //If connection is not successfull on this directory, try replicates (if replicates exists)
      if (!$ds && $ldap_method['id']>0) {
         foreach (getAllReplicateForAMaster($ldap_method['id']) as $replicate) {
            $ds = AuthLdap::connectToServer($replicate["host"], $replicate["port"],
                                 $ldap_method['rootdn'], $ldap_method['rootdn_password'],
                                 $ldap_method['use_tls'], $ldap_method['deref_option']);
            // Test with login and password of the user
            if (!$ds && !empty($login)) {
               $ds = AuthLdap::connectToServer($replicate["host"], $replicate["port"],
                                    $login, $password,
                                    $ldap_method['use_tls'], $ldap_method['deref_option']);
            }
            if ($ds) {
               return $ds;
            }
         }
      }
      return $ds;
   }

   static function getLdapServers () {
      return getAllDatasFromTable('glpi_authldaps');
   }

   /**
    * Is the LDAP authentication used ?
    *
    * @return boolean
   **/
   static function useAuthLdap() {
      global $DB;

      //Get all the ldap directories
      $sql = "SELECT count(*)
              FROM `glpi_authldaps`";
      $result = $DB->query($sql);
      if ($DB->result($result,0,0) > 0) {
         return true;
      }
      return false;
   }

   /**
    * Import a user from ldap
    * Check all the directories. When the user is found, then import it
    * @param $login : user login
   **/
   static function importUserFromServers($login) {
      global $LANG;

      $auth = new Auth;
      $auth->user_present = $auth->userExists($login);

      //If the user does not exists
      if ($auth->user_present == 0) {
         $auth->getAuthMethods();
         $ldap_methods = $auth->authtypes["ldap"];
         $userid = -1;

         foreach ($ldap_methods as $ldap_method) {
            $result=ldapImportUserByServerId($login, 0,$ldap_method["id"],true);
            if ($result != false) {
               return $result;
            }
         }
         addMessageAfterRedirect($LANG['login'][15],false,ERROR);
      } else {
         addMessageAfterRedirect($LANG['setup'][606],false,ERROR);
      }
      return false;
   }

   /**
    * Authentify a user by checking a specific directory
    * @param $auth : identification object
    * @param $login : user login
    * @param $password : user password
    * @param $ldap_method : ldap_method array to use
    * @return identification object
   **/
   static function ldapAuth($auth,$login,$password, $ldap_method) {

      $user_dn = $auth->connection_ldap($ldap_method,$login, $password);
      if ($user_dn) {
         $auth->auth_succeded = true;
         $auth->extauth = 1;
         $auth->user_present = $auth->user->getFromDBbyName(addslashes($login));
         $auth->user->getFromLDAP($auth->ldap_connection,$ldap_method, $user_dn, $login,
                                         $password);
         $auth->auth_parameters = $ldap_method;
         $auth->user->fields["authtype"] = AUTH_LDAP;
         $auth->user->fields["auths_id"] = $ldap_method["id"];
      }
      return $auth;
   }

   /**
    * Try to authentify a user by checking all the directories
    * @param $auth : identification object
    * @param $login : user login
    * @param $password : user password
    * @param $auths_id : auths_id already used for the user
    * @return identification object
   **/
   function tryLdapAuth($auth,$login,$password, $auths_id = 0) {

      //If no specific source is given, test all ldap directories
      if ($auths_id <= 0) {
         foreach  ($auth->authtypes["ldap"] as $ldap_method) {
            if (!$auth->auth_succeded) {
               $auth = AuthLdap::ldapAuth($auth, $login,$password,$ldap_method);
            } else {
               break;
            }
         }
      //Check if the ldap server indicated as the last good one still exists !
      } else if(array_key_exists($auths_id,$auth->authtypes["ldap"])) {
         //A specific ldap directory is given, test it and only this one !
         $auth = AuthLdap::ldapAuth($auth, $login,$password,
                                  $auth->authtypes["ldap"][$auths_id]);
      }
      return $auth;
   }

   /**
    * Get dn for a user
    *
    * @param $ds : LDAP link
    * @param $basedn : base dn used to search
    * @param $login_attr : attribute to store login
    * @param $login : user login
    * @param $condition : ldap condition used
    * @return dn of the user, else false
   **/
   static function searchUserDn($ds, $basedn, $login_attr, $login, $condition) {

      // Tenter une recherche pour essayer de retrouver le DN
      $filter = "($login_attr=$login)";

      if (!empty ($condition)) {
         $filter = "(& $filter $condition)";
      }
      if ($result = ldap_search($ds, $basedn, $filter, array ("dn", $login_attr),0,0)){
         $info = ldap_get_entries($ds, $result);
         if (is_array($info) AND $info['count'] == 1) {
            return $info[0]['dn'];
         } else { // Si echec, essayer de deviner le DN / Flat LDAP
            $dn = "$login_attr=$login," . $basedn;
            return $dn;
         }
      } else {
         return false;
      }
   }

   /**
    * Get infos for groups
    *
    * @param $ds : LDAP link
    * @param $basedn : base dn used to search
    * @param $group_dn : dn of the group
    * @param $condition : ldap condition used
    * @return group infos if found, else false
   **/
   static function getGroupByDn($ds, $basedn, $group_dn,$condition) {

      if($result = @ ldap_read($ds, $group_dn, "objectClass=*", array("cn"))) {
         $info = ldap_get_entries($ds, $result);
         if (is_array($info) AND $info['count'] == 1) {
            return $info[0];
         } else {
            return false;
         }
      }
      return false;
   }


}
?>