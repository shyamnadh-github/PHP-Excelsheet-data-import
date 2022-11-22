<?php
session_start();
include('dbconfig.php');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if(isset($_POST['save_excel_data']))
{
    $fileName = $_FILES['import_file']['name'];
    $file_ext = pathinfo($fileName, PATHINFO_EXTENSION);

    $allowed_ext = ['xls','xlsx'];

    if(in_array($file_ext, $allowed_ext))
    {
        $inputFileNamePath = $_FILES['import_file']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileNamePath);
        $data = $spreadsheet->getActiveSheet()->toArray();

        $count = "0";
        foreach($data as $row)
        {
            if($count > 0 && $row['0']!='')
            {
                $project_id          = $row['0'];
                $aem_email           = $row['1'];
                $aem_phone           = $row['2'];
                $ae_fullname         = $row['3'];
                $ae_firstname        = $row['4'];
                $ae_middlename       = $row['5'];
                $ae_lastname         = $row['6'];
                $ae_email            = $row['7'];
                $ae_mobile_number    = isset($row['8'])?$row['8'] : 0;
                $gender              = $row['9'];
                $highest_education   = $row['10'];
                $state               = $row['11'];
                $district            = $row['12'];
                $block               = $row['13'];
                $village             = $row['14'];
                $house_ownership     = $row['15'];
                $house_type          = $row['16'];
                $land_ownership      = $row['17'];
                $annual_income       = isset($row['18'])?$row['18'] : 0;
                $collateral          = $row['19'];
                $loan_taken          = $row['20'];
                $any_business_name   = $row['21'];
                $previous_occupation = $row['22'];
                $credit_linkage      = $row['23'];
                $pincode             = $row['24'];
                $period_residence    = $row['25'];
                $password            = isset($row['26'])?$row['26'] : NULL;
                $login_id            = $row['27'];

                $aem_id = "SELECT id FROM public.aem WHERE email = '$aem_email' AND number = '$aem_phone' ";
                $aem_sql = $conn->prepare($aem_id);
                $aem_sql->execute();
                $aem_result = $aem_sql->fetch(PDO::FETCH_ASSOC);
                if(empty($aem_result))
                {
                    echo "data in row[".$count."] of excelsheet is not present in table AEM";
                    exit;
                }
                else
                {
                $aem_id_sel = $aem_result['id'];
                $sel_query = "SELECT id FROM lead_to_project_to_aem WHERE project_id = $project_id AND aem_id = ".$aem_id_sel." ";
                $selectsql = $conn->prepare($sel_query);
                $selectsql->execute();
                $sel_result = $selectsql->fetch(PDO::FETCH_ASSOC);
                
                    if(empty($sel_result))
                    {
                        echo "aem_id and project_id in row[".$count."] of excelsheet doesnot match in table lead_to_project_to_aem";
                        exit;
                    }
                    else
                    {
                        $house_own = strtolower($house_ownership);
                        if($house_own == "yes")
                        {
                            $houseownership = "1";
                        }
                        else
                        {
                            $houseownership = "0";
                        }

                        $land_own = strtolower($land_ownership);
                        if($land_own == "yes")
                        {
                            $landownership = "1";
                        }
                        else
                        {
                            $landownership = "0";
                        }

                        $loan = strtolower($loan_taken);
                        if($loan == "yes")
                        {
                            $loantaken = "1";
                        }
                        else
                        {
                            $loantaken = "0";
                        }

                        $any_buss = strtolower($any_business_name);
                        if($any_buss == "yes")
                        {
                            $anybusinessname = "1";
                        }
                        else
                        {
                            $anybusinessname = "0";
                        }

                        $ae_insert = "INSERT INTO public.ae (name, first_name, middle_name, last_name, email, number, gender, highest_education,state, district, block, village, house_ownership, house_type, land_ownership, annual_income, collateral, loan_taken, any_business, previous_occupation, creditlinkage, role_id, isdeleted, pincode, period_of_residence) 
                        VALUES ('$ae_fullname', '$ae_firstname', '$ae_middlename', '$ae_lastname', '$ae_email', $ae_mobile_number, '$gender', '$highest_education', '$state', '$district', '$block', '$village', '$houseownership', '$house_type', '$landownership', $annual_income, '$collateral', '$loantaken', '$any_business_name', '$previous_occupation', '$credit_linkage', '5', 'false', '$pincode', '$period_residence') RETURNING id";
                        $ae_insert_sql = $conn->prepare($ae_insert);
                        $ae_insert_sql->execute();
                        $id = $ae_insert_sql->fetchAll(PDO::FETCH_ASSOC);
                        $ae_id = $id[0]['id'];
                        
                        $login_insert = "INSERT INTO public.login (username, password, usertable, userid, activestatus)
                        VALUES ('$login_id', MD5('$password'), 'ae', '$ae_id', 'true')";
                        $login_insert_sql = $conn->prepare($login_insert);
                        $login_insert_sql->execute();

                        $proj_to_aem_to_ae_insert = "INSERT INTO public.project_to_aem_to_ae (project_id, aem_id, ae_id)
                        VALUES($project_id, $aem_id_sel, $ae_id)";
                        $proj_to_aem_to_ae_insert_sql = $conn->prepare($proj_to_aem_to_ae_insert);
                        $proj_to_aem_to_ae_insert_sql->execute();
                        
                        $msg = true;
                        
                    }
                }
   
            }
            else
            {
                $count++;
            }
        }
        if(isset($msg))
        {
            $_SESSION['message'] = "Successfully Imported";
            // die('hello');
            header('Location: index.php');
            exit(0);
        }
        else
        {
            $_SESSION['message'] = "Not Imported";
            header('Location: index.php');
            exit(0);
        }
    }
    else
    {
        $_SESSION['message'] = "Invalid File";
        header('Location: index.php');
        exit(0);
    }
}
?>