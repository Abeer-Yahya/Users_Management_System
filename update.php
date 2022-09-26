<?php
// Include conn file
require_once "conn.php";
 
// Define variables and initialize with empty values
$fullName = $email= $mobile= $date= $password = $confirm_password = "";
$fullName_err= $email_err = $mobile_err= $date_err= $password_err = $confirm_password_err = "";
 
// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    // Get hidden input value
    $id = $_POST["id"];

    // Validate full name
    if(empty(trim($_POST["fullName"]))){
      $fullName_err = "Please enter your full name.";     
  } elseif(!preg_match('/^[A-zA-Z \s]{3,20}[A-zA-Z \s]{3,20}[A-zA-Z \s]{3,20} [A-zA-Z \s]{3,20}$/', trim($_POST["fullName"]))){
      $fullName_err = "full name can only contain letters in 4 syllables.";
  } 
   else{
      $fullName= trim($_POST["fullName"]);
  }
 // Validate email
 if(empty(trim($_POST["email"]))){
  $email_err = "Please enter an email.";
} elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
  $email_err = "Invalid email format.";
} else{
  // Prepare a select statement
  $sql = "SELECT id FROM users_info WHERE email = :email";
  
  if($stmt = $pdo->prepare($sql)){
      // Bind variables to the prepared statement as parameters
      $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
      
      // Set parameters
      $param_email = trim($_POST["email"]);
      
      // Attempt to execute the prepared statement
      if($stmt->execute()){
          if($stmt->rowCount() == 1){
              $email_err = "This email is already taken.";
          } else{
              $email= trim($_POST["email"]);
          }
      } else{
          echo "Oops! Something went wrong. Please try again later.";
      }

      // Close statement
      unset($stmt);
  }
}
    
  // Validate password

  $password = trim($_POST["password"]);
  $number = preg_match('@[0-9]@', $password);
  $uppercase = preg_match('@[A-Z]@', $password);
  $lowercase = preg_match('@[a-z]@', $password);
  $specialChars = preg_match('@[^\w]@', $password);
 if(strlen(trim($_POST["password"])) < 8 || !$number || !$uppercase || !$lowercase || !$specialChars){
      $password_err = "Password must be at least 8 characters and must contain at least one number, one upper case letter, one lower case letter and one special character..";
  } else{
      $password = trim($_POST["password"]);
  }
  
  // Validate confirm password
  if(empty(trim($_POST["confirm_password"]))){
      $confirm_password_err = "Please confirm password.";     
  } else{
      $confirm_password = trim($_POST["confirm_password"]);
      if(empty($password_err) && ($password != $confirm_password)){
          $confirm_password_err = "Password did not match.";
      }
  }
  
    
    
    // Check input errors before inserting in database
    if(empty($fullName_err) && empty($email_err) && empty($password_err)){
        // Prepare an update statement
        $sql = "UPDATE users_info SET fullName=:fullName, email=:email, password=:password WHERE id=:id";
 
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":fullName", $param_fullName);
            $stmt->bindParam(":email", $param_email);
            $stmt->bindParam(":password", $param_password);
            $stmt->bindParam(":id", $param_id);
            
            // Set parameters
            $param_fullName = $fullName;
            $param_email = $email;
            $param_password = $password;
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Records updated successfully. Redirect to landing page
                header("location: admin.php");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        unset($stmt);
    }
    
    // Close connection
    unset($pdo);
} else{
    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        $sql = "SELECT * FROM users_info WHERE id = :id";
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":id", $param_id);
            
            // Set parameters
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                    // Retrieve individual field value
                    $fullName = $row["fullName"];
                    $email = $row["email"];
                    $password = $row["password"];
                } else{
                    // URL doesn't contain valid id. Redirect to error page
                    header("location: error.php");
                    exit();
                }
                
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Close statement
        unset($stmt);
        
        // Close connection
        unset($pdo);
    }  else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: error.php");
        exit();
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Record</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .wrapper{
            width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5">Update Record</h2>
                    <p>Please edit the input values and submit to update the user information.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullName" class="form-control <?php echo (!empty($fullName_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $fullName; ?>">
                            <span class="invalid-feedback"><?php echo $fullName_err;?></span>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                            <span class="invalid-feedback"><?php echo $email_err;?></span>
                        </div>

                        <div class="form-group">
                            <label>Password</label>
                            <input type="text" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                            <span class="invalid-feedback"><?php echo $password_err;?></span>
                        </div>
                    
                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="admin.php" class="btn btn-secondary ml-2">Cancel</a>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>