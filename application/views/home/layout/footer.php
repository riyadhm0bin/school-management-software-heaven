

<?php 
$config = $this->home_model->whatsappChat();
$config = $this->application_model->checkArrayDBVal($config, 'whatsapp_chat');
if ($config['frontend_enable_chat'] == 1) {
?>
<div class="whatsapp-popup">
    <div class="whatsapp-button">
        <i class="fab fa-whatsapp i-open"></i>
        <i class="far fa-times-circle fa-fw i-close"></i>
    </div>
    <div class="popup-content">
        <div class="popup-content-header">
            <i class="fab fa-whatsapp"></i>
            <h5><?php echo $config['header_title'] ?><span><?php echo $config['subtitle'] ?></span></h5>
        </div>
        <div class="whatsapp-content">
            <ul>
            <?php $whatsappAgent = $this->home_model->whatsappAgent(); 
                foreach ($whatsappAgent as $key => $value) {
                    $online = "offline";
                    if (strtolower($value->weekend) != strtolower(date('l'))) {
                        $now = time();
                        $starttime = strtotime($value->start_time);
                        $endtime = strtotime($value->end_time);
                        if ($now >= $starttime && $now <= $endtime) {
                            $online = "online";
                        }
                    }
            ?>
                <li class="<?php echo $online ?>">
                    <a class="whatsapp-agent" href="javascript:void(0)" data-number="<?php echo $value->whataspp_number; ?>">
                        <div class="whatsapp-img">
                            <img src="<?php echo get_image_url('whatsapp_agent', $value->agent_image); ?>" class="whatsapp-avatar" width="60" height="60">
                        </div>
                        <div>
                            <span class="whatsapp-text">
                                <span class="whatsapp-label"><?php echo $value->agent_designation; ?> - <span class="status"><?php echo ucfirst($online) ?></span></span> <?php echo $value->agent_name; ?>
                            </span>
                        </div>
                    </a>
                </li>
            <?php } ?>
            </ul>
        </div>
        <div class="content-footer">
            <p><?php echo $config['footer_text'] ?></p>
        </div>
    </div>
</div>
<?php } ?>

<a href="#" class="back-to-top"><i class="far fa-arrow-alt-circle-up"></i></a>
<!-- JS Files -->
<script src="<?php echo base_url('assets/frontend/js/bootstrap.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/bootstrap-fileupload/bootstrap-fileupload.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/frontend/js/owl.carousel.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/frontend/plugins/shuffle/jquery.shuffle.modernizr.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/select2/js/select2.full.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/sweetalert/sweetalert.min.js');?>"></script>
<script src="<?php echo base_url('assets/frontend/plugins/magnific-popup/jquery.magnific-popup.min.js');?>"></script>
<script src="<?php echo base_url('assets/frontend/js/custom.js'); ?>"></script>

<?php
$alertclass = "";
if($this->session->flashdata('alert-message-success')){
    $alertclass = "success";
} else if ($this->session->flashdata('alert-message-error')){
    $alertclass = "error";
} else if ($this->session->flashdata('alert-message-info')){
    $alertclass = "info";
}
if($alertclass != ''):
    $alert_message = $this->session->flashdata('alert-message-'. $alertclass);
?>
<script type="text/javascript">
    swal({
        toast: true,
        position: 'top-end',
        type: '<?php echo $alertclass?>',
        title: '<?php echo $alert_message?>',
        confirmButtonClass: 'btn btn-1',
        buttonsStyling: false,
        timer: 8000
    })
</script>
<?php endif; ?>
