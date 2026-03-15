<!DOCTYPE html>
<?php 

include('func.php');  
include('newfunc.php');
$con=mysqli_connect("localhost","root","","myhmsdb");


  $pid = $_SESSION['pid'];
  $username = $_SESSION['username'];
  $email = $_SESSION['email'];
  $fname = $_SESSION['fname'];
  $gender = $_SESSION['gender'];
  $lname = $_SESSION['lname'];
  $contact = $_SESSION['contact'];



if(isset($_POST['app-submit']))
{
  $pid = $_SESSION['pid'];
  $username = $_SESSION['username'];
  $email = $_SESSION['email'];
  $fname = $_SESSION['fname'];
  $lname = $_SESSION['lname'];
  $gender = $_SESSION['gender'];
  $contact = $_SESSION['contact'];
  $doctor=$_POST['doctor'];
  $email=$_SESSION['email'];
  # $fees=$_POST['fees'];
  $docFees=$_POST['docFees'];

  $appdate=$_POST['appdate'];
  $apptime=$_POST['apptime'];
  $cur_date = date("Y-m-d");
  date_default_timezone_set('Asia/Kolkata');
  $cur_time = date("H:i:s");
  $apptime1 = strtotime($apptime);
  $appdate1 = strtotime($appdate);
	
  if(date("Y-m-d",$appdate1)>=$cur_date){
    if((date("Y-m-d",$appdate1)==$cur_date and date("H:i:s",$apptime1)>$cur_time) or date("Y-m-d",$appdate1)>$cur_date) {
      $check_query = mysqli_query($con,"select apptime from appointmenttb where doctor='$doctor' and appdate='$appdate' and apptime='$apptime'");

        if(mysqli_num_rows($check_query)==0){
          $query=mysqli_query($con,"insert into appointmenttb(pid,fname,lname,gender,email,contact,doctor,docFees,appdate,apptime,userStatus,doctorStatus) values($pid,'$fname','$lname','$gender','$email','$contact','$doctor','$docFees','$appdate','$apptime','1','1')");

          if($query)
          {
            echo "<script>alert('Your appointment successfully booked');</script>";
          }
          else{
            echo "<script>alert('Unable to process your request. Please try again!');</script>";
          }
      }
      else{
        echo "<script>alert('We are sorry to inform that the doctor is not available in this time or date. Please choose different time or date!');</script>";
      }
    }
    else{
      echo "<script>alert('Select a time or date in the future!');</script>";
    }
  }
  else{
      echo "<script>alert('Select a time or date in the future!');</script>";
  }
  
}

if(isset($_GET['cancel'])) {
    $query = mysqli_query($con, "update appointmenttb set userStatus='0' where ID = '".$_GET['ID']."'");
    if($query) {
        echo "<script>
                alert('Your appointment successfully cancelled');
                window.location.href = 'admin-panel.php'; 
              </script>";
    }
}


function generate_bill(){
  $con=mysqli_connect("localhost","root","","myhmsdb");
  $pid = $_SESSION['pid'];
  
  // URL se current Appointment ID nikalne ke liye
  $app_id = $_GET['ID']; 
  
  $output='';
  
  // Query ko WHERE clause ke saath sahi kiya gaya hai
  $query=mysqli_query($con,"select p.pid, p.ID, p.fname, p.lname, p.doctor, p.appdate, p.apptime, p.disease, p.allergy, p.prescription, a.docFees 
                            from prestb p 
                            inner join appointmenttb a on p.ID = a.ID 
                            where p.pid = '$pid' and p.ID = '$app_id' 
                            LIMIT 1");

  if(mysqli_num_rows($query) > 0){
    while($row = mysqli_fetch_array($query)){
      $output .= '
      <table border="0" cellspacing="5" cellpadding="5">
        <tr><td><b>Patient ID :</b></td><td>'.$row["pid"].'</td></tr>
        <tr><td><b>Appointment ID :</b></td><td>'.$row["ID"].'</td></tr>
        <tr><td><b>Patient Name :</b></td><td>'.$row["fname"].' '.$row["lname"].'</td></tr>
        <tr><td><b>Doctor Name :</b></td><td>'.$row["doctor"].'</td></tr>
        <tr><td colspan="2"><hr></td></tr>
        <tr><td><b>Appointment Date :</b></td><td>'.$row["appdate"].'</td></tr>
        <tr><td><b>Appointment Time :</b></td><td>'.$row["apptime"].'</td></tr>
        <tr><td><b>Disease :</b></td><td>'.$row["disease"].'</td></tr>
        <tr><td><b>Allergies :</b></td><td>'.$row["allergy"].'</td></tr>
        <tr><td><b>Prescription :</b></td><td>'.$row["prescription"].'</td></tr>
        <tr><td colspan="2"><hr></td></tr>
       <tr><td><b>Total Fees Paid :</b></td><td><b> Rs. '.number_format($row["docFees"]).'</b></td></tr>
      </table>';
    }
  } else {
    $output = "<h3>No data found for this appointment.</h3>";
  }
  
  return $output;
}


if(isset($_GET["generate_bill"])) {
    while (ob_get_level()) { ob_end_clean(); }
    $id = $_GET['ID'];
    mysqli_query($con, "update appointmenttb set paymentStatus='Paid' where ID='$id'");
    require_once("TCPDF/tcpdf.php");
    ob_start();
    
    $obj_pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $obj_pdf->SetTitle("Prescription_Bill");
    $obj_pdf->SetPrintHeader(false);
    $obj_pdf->SetPrintFooter(false);
    $obj_pdf->SetMargins(15, 10, 15);
    $obj_pdf->SetAutoPageBreak(TRUE, 15);
    $obj_pdf->SetFont('helvetica', '', 10);
    $obj_pdf->AddPage();

    // Custom CSS for better look
    $content = '
    <style>
        .header-title { font-size: 22pt; font-weight: bold; color: #3c50c1; }
        .rx-symbol { font-size: 35pt; color: #3c50c1; font-weight: bold; }
        .info-table td { padding: 4px; border-bottom: 1px solid #f0f0f0; }
        .footer-strip { background-color: #3c50c1; color: white; padding: 10px; font-size: 9pt; }
        .section-header { background-color: #f8f9fa; font-weight: bold; padding: 5px; color: #333; border-left: 4px solid #3c50c1; }
    </style>

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="75%">
                <span class="header-title">MULTI HOSPITAL</span><br/>
                <span style="font-size: 10pt; color: #555;">Medical Square, Bhubaneswar, Odisha | +91 1234567890</span>
            </td>
            <td width="25%" align="right"><span class="rx-symbol">Rx</span></td>
        </tr>
    </table>
    <div style="border-top: 2px solid #3c50c1; margin-top: 5px;"></div>

    <br/><br/>
    <table width="100%" class="info-table" cellpadding="5">
        <tr>
            <td width="15%"><b>Patient ID</b></td><td width="35%">: '.$_SESSION['pid'].'</td>
            <td width="15%"><b>Date</b></td><td width="35%">: '.date("d-M-Y").'</td>
        </tr>
        <tr>
            <td width="15%"><b>Patient Name</b></td><td width="35%">: '.$_SESSION['fname'].' '.$_SESSION['lname'].'</td>
            <td width="15%"><b>Appt. ID</b></td><td width="35%">: #'.$id.'</td>
        </tr>
    </table>

    <br/><br/>
    <div class="section-header"> &nbsp; CLINICAL DETAILS & PRESCRIPTION</div>
    <br/>';

    // Adding your generate_bill content here
    $content .= generate_bill(); 

    $content .= '
    <br/><br/>
    <table width="100%" cellpadding="5">
        <tr>
            <td width="60%" style="font-size: 8pt; color: #888;">
                * This is an electronically generated document.
            </td>
            <td width="40%" align="center">
                <br/><br/>
                <span style="border-top: 1px solid #333; padding-top: 5px;"><b>Authorized Signature</b></span>
            </td>
        </tr>
    </table>

    <br/><br/>
    <table width="100%" class="footer-strip">
        <tr>
            <td width="50%"><b>Emergency:</b> +91 1234567890</td>
            <td width="50%" align="right"><b>Web:</b> www.multihospital.com</td>
        </tr>
    </table>';

    $obj_pdf->writeHTML($content);
    ob_end_clean(); 
    $obj_pdf->Output("Bill_".$id.".pdf", 'I');
    exit(); 
}

function get_specs(){
  $con=mysqli_connect("localhost","root","","myhmsdb");
  $query=mysqli_query($con,"select username,spec from doctb");
  $docarray = array();
    while($row =mysqli_fetch_assoc($query))
    {
        $docarray[] = $row;
    }
    return json_encode($docarray);
}

?>
<html lang="en">
  <head>


    <!-- Required meta tags -->
    <meta charset="utf-8">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <!-- Bootstrap CSS -->
    
        <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">

    
  
    
    



    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
      <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
  <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Multi Hospital </a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <style >
    .bg-primary {
    background: -webkit-linear-gradient(left, #3931af, #00c6ff);
}
.list-group-item.active {
    z-index: 2;
    color: #fff;
    background-color: #342ac1;
    border-color: #007bff;
}
.text-primary {
    color: #342ac1!important;
}

.btn-primary{
  background-color: #3c50c1;
  border-color: #3c50c1;
}
  </style>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
     <ul class="navbar-nav mr-auto">
       <li class="nav-item">
        <a class="nav-link" href="logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Logout</a>
      </li>
       <li class="nav-item">
        <a class="nav-link" href="#"></a>
      </li>
    </ul>
  </div>
</nav>
  </head>
  <style type="text/css">
    button:hover{cursor:pointer;}
    #inputbtn:hover{cursor:pointer;}
  </style>
  <body style="padding-top:50px;">
  
   <div class="container-fluid" style="margin-top:50px;">
    <h3 style = "margin-left: 40%;  padding-bottom: 20px; font-family: 'IBM Plex Sans', sans-serif;"> Welcome &nbsp<?php echo $username ?> 
   </h3>
    <div class="row">
  <div class="col-md-4" style="max-width:25%; margin-top: 3%">
    <div class="list-group" id="list-tab" role="tablist">
      <a class="list-group-item list-group-item-action active" id="list-dash-list" data-toggle="list" href="#list-dash" role="tab" aria-controls="home">Dashboard</a>
      <a class="list-group-item list-group-item-action" id="list-home-list" data-toggle="list" href="#list-home" role="tab" aria-controls="home">Book Appointment</a>
      <a class="list-group-item list-group-item-action" href="#app-hist" id="list-pat-list" role="tab" data-toggle="list" aria-controls="home">Appointment History</a>
      <a class="list-group-item list-group-item-action" href="#list-pres" id="list-pres-list" role="tab" data-toggle="list" aria-controls="home">Prescriptions</a>
      
    </div><br>
  </div>
  <div class="col-md-8" style="margin-top: 3%;">
    <div class="tab-content" id="nav-tabContent" style="width: 950px;">


      <div class="tab-pane fade  show active" id="list-dash" role="tabpanel" aria-labelledby="list-dash-list">
        <div class="container-fluid container-fullw bg-white" >
              <div class="row">
               <div class="col-sm-4" style="left: 5%">
                  <div class="panel panel-white no-radius text-center">
                    <div class="panel-body">
                      <span class="fa-stack fa-2x"> <i class="fa fa-square fa-stack-2x text-primary"></i> <i class="fa fa-terminal fa-stack-1x fa-inverse"></i> </span>
                      <h4 class="StepTitle" style="margin-top: 5%;"> Book My Appointment</h4>
                      <script>
                        function clickDiv(id) {
                          document.querySelector(id).click();
                        }
                      </script>                      
                      <p class="links cl-effect-1">
                        <a href="#list-home" onclick="clickDiv('#list-home-list')">
                          Book Appointment
                        </a>
                      </p>
                    </div>
                  </div>
                </div>

                <div class="col-sm-4" style="left: 10%">
                  <div class="panel panel-white no-radius text-center">
                    <div class="panel-body" >
                      <span class="fa-stack fa-2x"> <i class="fa fa-square fa-stack-2x text-primary"></i> <i class="fa fa-paperclip fa-stack-1x fa-inverse"></i> </span>
                      <h4 class="StepTitle" style="margin-top: 5%;">My Appointments</h2>
                    
                      <p class="cl-effect-1">
                        <a href="#app-hist" onclick="clickDiv('#list-pat-list')">
                          View Appointment History
                        </a>
                      </p>
                    </div>
                  </div>
                </div>
                </div>

                <div class="col-sm-4" style="left: 20%;margin-top:5%">
                  <div class="panel panel-white no-radius text-center">
                    <div class="panel-body" >
                      <span class="fa-stack fa-2x"> <i class="fa fa-square fa-stack-2x text-primary"></i> <i class="fa fa-list-ul fa-stack-1x fa-inverse"></i> </span>
                      <h4 class="StepTitle" style="margin-top: 5%;">Prescriptions</h2>
                    
                      <p class="cl-effect-1">
                        <a href="#list-pres" onclick="clickDiv('#list-pres-list')">
                          View Prescription List
                        </a>
                      </p>
                    </div>
                  </div>
                </div>
                
         
            </div>
          </div>





      <div class="tab-pane fade" id="list-home" role="tabpanel" aria-labelledby="list-home-list">
        <div class="container-fluid">
          <div class="card">
            <div class="card-body">
              <center><h4>Create an appointment</h4></center><br>
              <form class="form-group" method="post" action="admin-panel.php">
                <div class="row">
                  
                  <!-- <?php

                        $con=mysqli_connect("localhost","root","","myhmsdb");
                        $query=mysqli_query($con,"select username,spec from doctb");
                        $docarray = array();
                          while($row =mysqli_fetch_assoc($query))
                          {
                              $docarray[] = $row;
                          }
                          echo json_encode($docarray);

                  ?> -->
        

                    <div class="col-md-4">
                          <label for="spec">Specialization:</label>
                        </div>
                        <div class="col-md-8">
                          <select name="spec" class="form-control" id="spec">
                              <option value="" disabled selected>Select Specialization</option>
                              <?php 
                              display_specs();
                              ?>
                          </select>
                        </div>

                        <br><br>

                        <script>
                      document.getElementById('spec').onchange = function foo() {
                        let spec = this.value;   
                        console.log(spec)
                        let docs = [...document.getElementById('doctor').options];
                        
                        docs.forEach((el, ind, arr)=>{
                          arr[ind].setAttribute("style","");
                          if (el.getAttribute("data-spec") != spec ) {
                            arr[ind].setAttribute("style","display: none");
                          }
                        });
                      };

                  </script>

              <div class="col-md-4"><label for="doctor">Doctors:</label></div>
                <div class="col-md-8">
                    <select name="doctor" class="form-control" id="doctor" required="required">
                      <option value="" disabled selected>Select Doctor</option>
                
                      <?php display_docs(); ?>
                    </select>
                  </div><br/><br/> 


                        <script>
  document.getElementById('doctor').onchange = function() {
    // Bina querySelector ke seedhe index se data uthao (Sabse Best Tarika)
    var selection = this.options[this.selectedIndex].getAttribute('data-value');
    
    // Fees input box mein value daal do
    document.getElementById('docFees').value = selection;
  };
</script>

                  
                  

                  
                        <!-- <div class="col-md-4"><label for="doctor">Doctors:</label></div>
                                <div class="col-md-8">
                                    <select name="doctor" class="form-control" id="doctor1" required="required">
                                      <option value="" disabled selected>Select Doctor</option>
                                      
                                    </select>
                                </div>
                                <br><br> -->

                                <!-- <script>
                                  document.getElementById("spec").onchange = function updateSpecs(event) {
                                      var selected = document.querySelector(`[data-value=${this.value}]`).getAttribute("value");
                                      console.log(selected);

                                      var options = document.getElementById("doctor1").querySelectorAll("option");

                                      for (i = 0; i < options.length; i++) {
                                        var currentOption = options[i];
                                        var category = options[i].getAttribute("data-spec");

                                        if (category == selected) {
                                          currentOption.style.display = "block";
                                        } else {
                                          currentOption.style.display = "none";
                                        }
                                      }
                                    }
                                </script> -->

                        
                    <!-- <script>
                    let data = 
                
              document.getElementById('spec').onchange = function updateSpecs(e) {
                let values = data.filter(obj => obj.spec == this.value).map(o => o.username);   
                document.getElementById('doctor1').value = document.querySelector(`[value=${values}]`).getAttribute('data-value');
              };
            </script> -->


                  
                  <div class="col-md-4"><label for="consultancyfees">
                                Consultancy Fees
                              </label></div>
                              <div class="col-md-8">
                              <!-- <div id="docFees">Select a doctor</div> -->
                              <input class="form-control" type="text" name="docFees" id="docFees" readonly="readonly"/>
                  </div><br><br>

                  <div class="col-md-4"><label>Appointment Date</label></div>
                  <div class="col-md-8"><input type="date" class="form-control datepicker" name="appdate"></div><br><br>

                  <div class="col-md-4"><label>Appointment Time</label></div>
                  <div class="col-md-8">
                    <!-- <input type="time" class="form-control" name="apptime"> -->
                    <select name="apptime" class="form-control" id="apptime" required="required">
                      <option value="" disabled selected>Select Time</option>
                      <option value="08:00:00">8:00 AM</option>
                      <option value="10:00:00">10:00 AM</option>
                      <option value="12:00:00">12:00 PM</option>
                      <option value="14:00:00">2:00 PM</option>
                      <option value="16:00:00">4:00 PM</option>
                      <option value="18:00:00">6:00 PM</option>
                      <option value="19:00:00">8:00 PM</option>
                    </select>

                  </div><br><br>

                  <div class="col-md-4">
                    <input type="submit" name="app-submit" value="Create new entry" class="btn btn-primary" id="inputbtn">
                  </div>
                  <div class="col-md-8"></div>                  
                </div>
              </form>
            </div>
          </div>
        </div><br>
      </div>
      
<div class="tab-pane fade" id="app-hist" role="tabpanel" aria-labelledby="list-pat-list">
        
              <table class="table table-hover">
                <thead>
                  <tr>
                    
                    <th scope="col">Doctor Name</th>
                    <th scope="col">Consultancy Fees</th>
                    <th scope="col">Appointment Date</th>
                    <th scope="col">Appointment Time</th>
                    <th scope="col">Current Status</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
  <?php 
    $con=mysqli_connect("localhost","root","","myhmsdb");
    $query = "select ID,doctor,docFees,appdate,apptime,userStatus,doctorStatus from appointmenttb where fname ='$fname' and lname='$lname';";
    $result = mysqli_query($con,$query);
    while ($row = mysqli_fetch_array($result)){
  ?>
    <tr>
      <td><?php echo $row['doctor'];?></td>
      <td><?php echo $row['docFees'];?></td>
      <td><?php echo $row['appdate'];?></td>
      <td><?php echo $row['apptime'];?></td>
      <td>
        <?php 
          if(($row['userStatus']==1) && ($row['doctorStatus']==1)) echo "Active";
          else if(($row['userStatus']==0)) echo "Cancelled by You";
          else if(($row['doctorStatus']==0)) echo "Cancelled by Doctor";
          else if(($row['doctorStatus']==2)) echo "Closed / Completed";
        ?>
      </td>
      <td>
        <?php if(($row['userStatus']==1) && ($row['doctorStatus']==1)) { ?>
          <a href="admin-panel.php?ID=<?php echo $row['ID']?>&cancel=update" 
             onClick="return confirm('Are you sure?')">
             <button class="btn btn-danger">Cancel</button>
          </a>
        <?php } else { echo "No Action"; } ?>
      </td>
    </tr>
  <?php } ?>
</tbody>
              </table>
        <br>
      </div>



      <div class="tab-pane fade" id="list-pres" role="tabpanel" aria-labelledby="list-pres-list">
        
              <table class="table table-hover">
                <thead>
                  <tr>
                    
                    <th scope="col">Doctor Name</th>
                    <th scope="col">Appointment ID</th>
                    <th scope="col">Appointment Date</th>
                    <th scope="col">Appointment Time</th>
                    <th scope="col">Diseases</th>
                    <th scope="col">Allergies</th>
                    <th scope="col">Prescriptions</th>
                    <th scope="col">Bill Payment</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 

                    $con=mysqli_connect("localhost","root","","myhmsdb");
                    global $con;

                    $query = "select doctor,ID,appdate,apptime,disease,allergy,prescription from prestb where pid='$pid';";
                    
                    $result = mysqli_query($con,$query);
                    if(!$result){
                      echo mysqli_error($con);
                    }
                    

                    while ($row = mysqli_fetch_array($result)){
                  ?>
                      <tr>
    <td><?php echo $row['doctor'];?></td>

    <td><?php echo $row['ID'];?></td>

    <td><?php echo $row['appdate'];?></td>

    <td><?php echo $row['apptime'];?></td>

    <td><?php echo $row['disease'];?></td>

    <td><?php echo $row['allergy'];?></td>

    <td><?php echo $row['prescription'];?></td>
    

 <td>
  <?php 
    // Status check kar rahe hain
    $status_query = mysqli_query($con, "select paymentStatus from appointmenttb where ID='".$row['ID']."'");
    $status_row = mysqli_fetch_array($status_query);

    if(isset($status_row['paymentStatus']) && $status_row['paymentStatus'] == "Paid") {
        // Agar Paid hai, toh Download link dikhao (taaki patient bill dekh sake)
        echo '<a href="admin-panel.php?ID='.$row['ID'].'&generate_bill=1" target="_blank" class="btn btn-secondary" style="color:white;">Billed (View PDF)</a>';
    } else { ?>
        <form method="get" action="admin-panel.php" target="_blank">
            <input type="hidden" name="ID" value="<?php echo $row['ID']?>"/>
            <input type="submit" name="generate_bill" class="btn btn-success" value="Pay Bill" 
                   onclick="setTimeout(function(){location.reload();}, 1000);"/>
        </form>
  <?php } ?>
</td>
</tr>

                    
                      </tr>
                    <?php }
                    ?>
                </tbody>
              </table>
        <br>
      </div>




      <div class="tab-pane fade" id="list-messages" role="tabpanel" aria-labelledby="list-messages-list">...</div>
      <div class="tab-pane fade" id="list-settings" role="tabpanel" aria-labelledby="list-settings-list">
        <form class="form-group" method="post" action="func.php">
          <label>Doctors name: </label>
          <input type="text" name="name" placeholder="Enter doctors name" class="form-control">
          <br>
          <input type="submit" name="doc_sub" value="Add Doctor" class="btn btn-primary">
        </form>
      </div>
       <div class="tab-pane fade" id="list-attend" role="tabpanel" aria-labelledby="list-attend-list">...</div>
    </div>
  </div>
</div>
   </div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.10.1/sweetalert2.all.min.js">
   </script>



  </body>
</html>
