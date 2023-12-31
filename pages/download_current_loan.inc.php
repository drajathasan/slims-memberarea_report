<?php
/**
 *
 * Download CSV for member loan history
 * Copyright (C) 2009  Hendro Wicaksono (hendrowicaksono@yahoo.com)
 * Based on member.inc.php
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

 use SLiMS\Pdf\Factory;

 // be sure that this file not accessed directly
 defined('INDEX_AUTH') or die("can not access this file directly");
 
 Factory::registerProvider('Pdf', SlimsPdf::class);
 Factory::useProvider('Pdf');

// required file
require LIB.'member_logon.inc.php';
// check if member already logged in
$is_member_login = utility::isMemberLogin();


// check if member already login
if (!$is_member_login) {
    header ("location:index.php");
} else {

    /* Experimental Loan History - start */
    function showLoanList($num_recs_show = 1000000)
    {
        global $dbs;
        require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
        require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
        require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
        require SIMBIO.'simbio_UTILS/simbio_date.inc.php';

        // table spec
        $_table_spec = 'loan AS l
            LEFT JOIN member AS m ON l.member_id=m.member_id
            LEFT JOIN item AS i ON l.item_code=i.item_code
            LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id';

        // create datagrid
        $_loan_list = new simbio_datagrid();
        $_loan_list->disable_paging = true;
        $_loan_list->table_ID = 'loanlist';
        $_loan_list->setSQLColumn('l.item_code AS \''.__('Item Code').'\'',
            'b.title AS \''.__('Title').'\'',
            'l.loan_date AS \''.__('Loan Date').'\'',
            'l.due_date AS \''.__('Due Date').'\'');
        $_loan_list->setSQLorder('l.loan_date DESC');
        $_criteria = sprintf('m.member_id=\'%s\' AND l.is_lent=1 AND is_return=0 ', $_SESSION['mid']);
        $_loan_list->setSQLCriteria($_criteria);

        /* callback function to show overdue */
        function showOverdue($obj_db, $array_data)
        {
            $_curr_date = date('Y-m-d');
            if (simbio_date::compareDates($array_data[3], $_curr_date) == $_curr_date) {
                #return '<strong style="color: #f00;">'.$array_data[3].' '.__('OVERDUED').'</strong>';
            } else {
                return $array_data[3];
            }
        }

        // modify column value
        $_loan_list->modifyColumnContent(3, 'callback{showOverdue}');
        // set table and table header attributes
        $_loan_list->table_attr = 'align="center" class="memberLoanList" cellpadding="5" cellspacing="0"';
        $_loan_list->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
        $_loan_list->using_AJAX = false;
        // return the result
        $_result = $_loan_list->createDataGrid($dbs, $_table_spec, $num_recs_show);
        $_result = '<div class="memberLoanListInfo">'.$_loan_list->num_rows.' '.__('Current Loan item(s)').'</div>'."\n".$_result;
        return $_result;
    }
    /* Experimental Loan History - end */

    function showMemberDetail()
    {
        // show the member information
        $_detail = '<table class="memberDetail table table-striped" cellpadding="5" cellspacing="0">' . "\n";
        // member notes and pending information
        if ($_SESSION['m_membership_pending'] || $_SESSION['m_is_expired']) {
            $_detail .= '<tr>' . "\n";
            $_detail .= '<td class="key alterCell" width="15%"><strong>Notes</strong></td><td class="value alterCell2" colspan="3">';
            if ($_SESSION['m_is_expired']) {
                $_detail .= '<div style="color: #f00;">' . __('Your Membership Already EXPIRED! Please extend your membership.') . '</div>';
            }
            if ($_SESSION['m_membership_pending']) {
                $_detail .= '<div style="color: #f00;">' . __('Membership currently in pending state, no loan transaction can be made yet.') . '</div>';
            }
            $_detail .= '</td>';
            $_detail .= '</tr>' . "\n";
        }
        $_detail .= '<tr>' . "\n";
        $_detail .= '<td class="key alterCell" width="15%"><strong>' . __('Member Name') . '</strong></td><td class="value alterCell2" width="30%">' . $_SESSION['m_name'] . '</td>';
        $_detail .= '<td class="key alterCell" width="15%"><strong>' . __('Member ID') . '</strong></td><td class="value alterCell2" width="30%">' . $_SESSION['mid'] . '</td>';
        $_detail .= '</tr>' . "\n";
        $_detail .= '<tr>' . "\n";
        $_detail .= '<td class="key alterCell" width="15%"><strong>' . __('Member Email') . '</strong></td><td class="value alterCell2" width="30%">' . $_SESSION['m_email'] . '</td>';
        $_detail .= '<td class="key alterCell" width="15%"><strong>' . __('Member Type') . '</strong></td><td class="value alterCell2" width="30%">' . $_SESSION['m_member_type'] . '</td>';
        $_detail .= '</tr>' . "\n";
        $_detail .= '<tr>' . "\n";
        $_detail .= '<td class="key alterCell" width="15%"><strong>' . __('Register Date') . '</strong></td><td class="value alterCell2" width="30%">' . $_SESSION['m_register_date'] . '</td>';
        $_detail .= '<td class="key alterCell" width="15%"><strong>' . __('Expiry Date') . '</strong></td><td class="value alterCell2" width="30%">' . $_SESSION['m_expire_date'] . '</td>';
        $_detail .= '</tr>' . "\n";
        $_detail .= '<tr>' . "\n";
        $_detail .= '<td class="key alterCell" width="15%"><strong>' . __('Institution') . '</strong></td>'
            . '<td class="value alterCell2" colspan="3">' . $_SESSION['m_institution'] . '</td>';
        $_detail .= '</tr>' . "\n";
        $_detail .= '</table>' . "\n";


        return $_detail;
    }

    // show all
    #echo '<h3 class="memberInfoHead">'.__('Your Current Loan').'</h3>'."\n";
    #echo showLoanList();
    $download = '<style>a { text-decoration: none; } .memberDetail {width: 100%} .memberLoanList {width: 100%; margin-top: 10px} td {border: 1px solid black;}</style>';
    $download .= '<img style="width: 500px;" src="data:image/png;base64, ' . base64_encode(file_get_contents(__DIR__ . '/../static/header.png')) . '"/>';
    $download .= '<h3 class="memberInfoHead">'.__('Member Detail').'</h3>'."\n";
    $download .= showMemberDetail();
    $download .= '<h3 class="memberInfoHead">'.__('Your Current Loan').'</h3>'."\n";
    $download .= showLoanList();

    // exit($download);
    ob_end_clean();
    ob_start();
    $pdf = Factory::setContent(['html' => $download]);

    if (isDev()) $pdf->stream();
    else $pdf->download('daftar-peminjaman-terkini-' . $_SESSION['mid'] . '.pdf');
}
