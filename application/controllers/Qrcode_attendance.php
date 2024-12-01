<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Ramom School QR Attendance
 * @version : 1.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Qr_code_attendance.php
 * @copyright : Reserved RamomCoder Team
 */

class Qrcode_attendance extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!moduleIsEnabled('qr_code_attendance')) {
            access_denied();
        }
        $this->load->model('attendance_model');
        $this->load->model('qrcode_attendance_model');
        $this->load->model('sms_model');
    }

    public function index()
    {
        if (get_loggedin_id()) {
            redirect(base_url('dashboard'), 'refresh');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function student_entry()
    {
        if (!get_permission('qr_code_student_attendance', 'is_add')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        $this->data['headerelements'] = array(
            'css' => array(
                'css/qr-code.css',
            ),
            'js' => array(
                'vendor/qrcode/qrcode.min.js',
                'js/qrcode_attendance.js',
            ),
        );
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'qrcode_attendance/student_entries';
        $this->data['main_menu'] = 'qr_attendance';
        $this->load->view('layout/index', $this->data);
    }

    public function getStudentByQrcode()
    {
        if ($_POST) {
            if (!get_permission('qr_code_student_attendance', 'is_add')) {
                ajax_access_denied();
            }

            $enrollID = trim(base64_decode($this->input->post('data')));
            $enrollID = explode("-", $enrollID);
            if ($enrollID[0] != 's') {
                $data['status'] = 'failed';
                $data['message'] = "<i class='fas fa-exclamation-triangle'></i> QR code is invalid.";
                echo json_encode($data);
                exit;
            }
            $enrollID = $enrollID[1];
            $enrollID = intval($enrollID);
            $data = [];
            $attendance = $this->db->where(array('enroll_id' => $enrollID, 'date' => date('Y-m-d')))->get('student_attendance')->row();
            if (!empty($attendance)) {
                $data['status'] = 'failed';
                if ($attendance->qr_code == 1) {
                    $data['message'] = "<i class='fas fa-exclamation-triangle'></i> Attendance has already been taken.";
                } else {
                    $data['message'] = "<i class='fas fa-exclamation-triangle'></i> Attendance has already been taken by manually.";
                }
                echo json_encode($data);
                exit();
            }

            $row = $this->qrcode_attendance_model->getStudentDetailsByEid($enrollID);
            if (empty($row)) {
                $data['status'] = 'failed';
                $data['message'] = "<i class='fas fa-exclamation-triangle'></i> QR code is invalid / student not found.";
            } else {
                $data['status'] = 'successful';
                $data['photo'] = get_image_url('student', $row->photo);
                $data['full_name'] = $row->first_name . " " . $row->last_name;
                $data['student_category'] = $row->cname;
                $data['register_no'] = $row->register_no;
                $data['roll'] = $row->roll;
                $data['admission_date'] = empty($row->admission_date) ? "N/A" : _d($row->admission_date);
                $data['birthday'] = empty($row->birthday) ? "N/A" : _d($row->birthday);
                $data['class_name'] = $row->class_name;
                $data['section_name'] = $row->section_name;
                $data['email'] = $row->email;
            }
            echo json_encode($data);
        }
    }

    // student submitted attendance all data are prepared and stored in the database here
    public function setStuAttendanceByQrcode()
    {
        if ($_POST) {
            if (!get_permission('qr_code_student_attendance', 'is_add')) {
                ajax_access_denied();
            }
            $data = [];
            $enrollID = trim(base64_decode($this->input->post('data')));
            $enrollID = explode("-", $enrollID);
            if ($enrollID[0] != 's') {
                $data['status'] = 'failed';
                $data['message'] = "<i class='fas fa-exclamation-triangle'></i> QR code is invalid.";
                echo json_encode($data);
                exit;
            }
            $enrollID = $enrollID[1];

            $attendanceRemark = $this->input->post('attendanceRemark');
            $enrollID = intval($enrollID);
            $stuDetail = $this->qrcode_attendance_model->getStudentDetailsByEid($enrollID);
            $attendance = $this->db->where(array('enroll_id' => $enrollID, 'date' => date('Y-m-d')))->get('student_attendance')->row();
            if (empty($attendance)) {
                $data['status'] = 1;
                $attendance = (isset($_POST['late']) ? 'L' : 'P');
                $arrayAttendance = array(
                    'enroll_id' => $enrollID,
                    'status' => $attendance,
                    'qr_code' => "1",
                    'remark' => $attendanceRemark,
                    'date' => date('Y-m-d'),
                    'branch_id' => $stuDetail->branch_id,
                );
                $this->db->insert('student_attendance', $arrayAttendance);
            } else {
                $data['status'] = 0;
            }
            echo json_encode($data);
        }
    }

    public function getStuListDT()
    {
        if ($_POST) {
            $postData = $this->input->post();
            echo $this->qrcode_attendance_model->getStuListDT($postData);
        }
    }

    public function staff_entry()
    {
        if (!get_permission('qr_code_employee_attendance', 'is_add')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        $this->data['headerelements'] = array(
            'css' => array(
                'css/qr-code.css',
            ),
            'js' => array(
                'vendor/qrcode/qrcode.min.js',
                'js/qrcode_attendance.js',
            ),
        );
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('employee_attendance');
        $this->data['sub_page'] = 'qrcode_attendance/staff_entries';
        $this->data['main_menu'] = 'qr_attendance';
        $this->load->view('layout/index', $this->data);
    }

    public function getStaffByQrcode()
    {
        if ($_POST) {
            if (!get_permission('qr_code_employee_attendance', 'is_add')) {
                ajax_access_denied();
            }

            $staffID = trim(base64_decode($this->input->post('data')));
            $staffID = explode("-", $staffID);
            if ($staffID[0] != 'e') {
                $data['status'] = 'failed';
                $data['message'] = "<i class='fas fa-exclamation-triangle'></i> QR code is invalid.";
                echo json_encode($data);
                exit;
            }
            $staffID = $staffID[1];
            $staffID = intval($staffID);
            $inOutTime = trim($this->input->post('in_out_time'));
            $data = [];
            if ($inOutTime == 'in_time') {
                $this->db->where('in_time !=', '');
            }
            if ($inOutTime == 'out_time') {
                $this->db->where('out_time !=', '');
            }
            $this->db->where(array('staff_id' => $staffID, 'date' => date('Y-m-d')));
            $attendance = $this->db->get('staff_attendance')->row();
            if (!empty($attendance)) {
                $data['status'] = 'failed';
                if ($attendance->qr_code == 1) {
                    $data['message'] = "<i class='fas fa-exclamation-triangle'></i> Attendance has already been taken.";
                } else {
                    $data['message'] = "<i class='fas fa-exclamation-triangle'></i> Attendance has already been taken by manually.";
                }
                echo json_encode($data);
                exit();
            }

            $row = $this->qrcode_attendance_model->getSingleStaff($staffID);
            if (empty($row)) {
                $data['status'] = 'failed';
                $data['message'] = "<i class='fas fa-exclamation-triangle'></i> QR code is invalid / student not found.";
            } else {
                $data['status'] = 'successful';
                $data['photo'] = get_image_url('staff', $row->photo);
                $data['name'] = $row->name;
                $data['role'] = $row->role;
                $data['staff_id'] = $row->staff_id;
                $data['joining_date'] = _d($row->joining_date);
                $data['department'] = $row->department_name;
                $data['designation'] = $row->designation_name;
                $data['gender'] = ucfirst($row->sex);
                $data['blood_group'] = (empty($row->blood_group) ? '-' : $row->blood_group);
                $data['email'] = $row->email;
            }
            echo json_encode($data);
        }
    }

    // Staff submitted attendance all data are prepared and stored in the database here
    public function setStaffAttendanceByQrcode()
    {
        if ($_POST) {
            if (!get_permission('qr_code_employee_attendance', 'is_add')) {
                ajax_access_denied();
            }
            $data = [];
            $staffID = trim(base64_decode($this->input->post('data')));
            $staffID = explode("-", $staffID);
            if ($staffID[0] != 'e') {
                $data['status'] = 'failed';
                $data['message'] = "<i class='fas fa-exclamation-triangle'></i> QR code is invalid.";
                echo json_encode($data);
                exit;
            }
            $staffID = $staffID[1];
            $inOutTime = trim($this->input->post('in_out_time'));
            if ($inOutTime == 'in_time') {
                $attendanceStatus = (isset($_POST['late']) ? 'L' : 'P');
            } else {
                $attendanceStatus = (isset($_POST['halfday']) ? 'HD' : '');
            }
            $attendanceRemark = $this->input->post('attendanceRemark');
            $staffID = intval($staffID);
            $stuDetail = $this->qrcode_attendance_model->getSingleStaff($staffID);
            $attendance = $this->db->where(array('staff_id' => $staffID, 'date' => date('Y-m-d')))->get('staff_attendance')->row();
            if (empty($attendance)) {
                $data['status'] = 1;
                $arrayAttendance = array(
                    'staff_id' => $staffID,
                    'status' => $attendanceStatus,
                    'qr_code' => "1",
                    'remark' => $attendanceRemark,
                    'date' => date('Y-m-d'),
                    'branch_id' => $stuDetail->branch_id,
                );
                $arrayAttendance[$inOutTime] = date('H:i:s');
                $this->db->insert('staff_attendance', $arrayAttendance);
            } else {
                $data['status'] = 1;
                $update = array();
                $update[$inOutTime] = date('H:i:s');
                if (!empty($attendanceRemark)) {
                    $update['remark'] = $attendanceRemark;
                }
                if (!empty($attendanceStatus)) {
                    $update['status'] = $attendanceStatus;
                }
                $this->db->where('id', $attendance->id)->update('staff_attendance', $update);
            }
            echo json_encode($data);
        }
    }

    public function getStaffListDT()
    {
        if ($_POST) {
            $postData = $this->input->post();
            echo $this->qrcode_attendance_model->getStaffListDT($postData);
        }
    }

    public function studentbydate()
    {
        if (!get_permission('qr_code_student_attendance_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        $this->data['getWeekends'] = $this->application_model->getWeekends($branchID);
        $this->data['getHolidays'] = $this->attendance_model->getHolidays($branchID);
        if ($_POST) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('date', translate('date'), 'trim|required|callback_get_valid_date');
            if ($this->form_validation->run() == true) {
                $this->data['class_id'] = $this->input->post('class_id');
                $this->data['section_id'] = $this->input->post('section_id');
                $this->data['date'] = $this->input->post('date');
                $this->data['attendancelist'] = $this->qrcode_attendance_model->getDailyStudentReport($branchID, $this->data['class_id'], $this->data['section_id'], $this->data['date']);
            }
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student') . ' ' . translate('daily_reports');
        $this->data['sub_page'] = 'qrcode_attendance/studentbydate';
        $this->data['main_menu'] = 'qr_attendance_report';
        $this->load->view('layout/index', $this->data);
    }

    public function staffbydate()
    {
        if (!get_permission('qr_code_employee_attendance_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        $this->data['getWeekends'] = $this->application_model->getWeekends($branchID);
        $this->data['getHolidays'] = $this->attendance_model->getHolidays($branchID);
        if ($_POST) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('date', translate('date'), 'trim|required|callback_get_valid_date');
            if ($this->form_validation->run() == true) {
                $this->data['staff_role'] = $this->input->post('staff_role');
                $this->data['date'] = $this->input->post('date');
                $this->data['attendancelist'] = $this->qrcode_attendance_model->getDailyStaffReport($branchID, $this->data['staff_role'], $this->data['date']);
            }
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('employee') . ' ' . translate('daily_reports');
        $this->data['sub_page'] = 'qrcode_attendance/staffbydate';
        $this->data['main_menu'] = 'qr_attendance_report';
        $this->load->view('layout/index', $this->data);
    }

    public function get_valid_date($date)
    {
        $present_date = date('Y-m-d');
        $date = date("Y-m-d", strtotime($date));
        if ($date > $present_date) {
            $this->form_validation->set_message("get_valid_date", "Please Enter Correct Date");
            return false;
        } else {
            return true;
        }
    }

    public function check_holiday($date)
    {
        $branchID = $this->application_model->get_branch_id();
        $getHolidays = $this->attendance_model->getHolidays($branchID);
        $getHolidaysArray = explode('","', $getHolidays);

        if (!empty($getHolidaysArray)) {
            if (in_array($date, $getHolidaysArray)) {
                $this->form_validation->set_message('check_holiday', 'You have selected a holiday.');
                return false;
            } else {
                return true;
            }
        }
    }

    public function check_weekendday($date)
    {
        $branchID = $this->application_model->get_branch_id();
        $getWeekendDays = $this->attendance_model->getWeekendDaysSession($branchID);
        if (!empty($getWeekendDays)) {
            if (in_array($date, $getWeekendDays)) {
                $this->form_validation->set_message('check_weekendday', "You have selected a weekend date.");
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

}
