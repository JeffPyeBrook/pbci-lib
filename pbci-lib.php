<?php
/*
** Copyright 2010-2014, Pye Brook Company, Inc.
**
**
** This software is provided under the GNU General Public License, version
** 2 (GPLv2), that covers its  copying, distribution and modification. The 
** GPLv2 license specifically states that it only covers only copying,
** distribution and modification activities. The GPLv2 further states that 
** all other activities are outside of the scope of the GPLv2.
**
** All activities outside the scope of the GPLv2 are covered by the Pye Brook
** Company, Inc. License. Any right not explicitly granted by the GPLv2, and 
** not explicitly granted by the Pye Brook Company, Inc. License are reserved
** by the Pye Brook Company, Inc.
**
** This software is copyrighted and the property of Pye Brook Company, Inc.
**
** Contact Pye Brook Company, Inc. at info@pyebrook.com for more information.
**
** This program is distributed in the hope that it will be useful, but WITHOUT ANY 
** WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR 
** A PARTICULAR PURPOSE. 
**
*/

$pbci_file_to_check = dirname( __FILE__) . '/pbci-log.php';
if (file_exists(  $pbci_file_to_check ) ) {
	include_once( $pbci_file_to_check );
}

$pbci_file_to_check = dirname( __FILE__) . '/nag.php';
if (file_exists(  $pbci_file_to_check ) ) {
	include_once( $pbci_file_to_check );
}

$pbci_file_to_check = dirname( __FILE__) . '/about-support.php';
if (file_exists(  $pbci_file_to_check ) ) {
	include_once( $pbci_file_to_check );
}

$pbci_file_to_check = dirname( __FILE__) . '/pbci-check-for-update.php';
if (file_exists(  $pbci_file_to_check ) ) {
	include_once( $pbci_file_to_check );
}



