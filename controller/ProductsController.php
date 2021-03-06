<?php

 // start session
 

include_once('model/EstoreDB.php');
include_once('utils/Validation.php');

/**
 * Description of ProductsController
 */
class ProductsController {

    function __construct() {
        
    }

    /**
     * addBooking function manages data model
     * and view for adding a new booking
     * record
     */
    function addProduct() {


        $path = '../';
        
        $userID = $_SESSION['userID'];
        
        // initialise form variables
        $Description = $Category = $Quantity = $CostPrice = $SellingPrice = '';

        // if form is submitted
        if (isset($_POST['addProduct'])) {

            // remove key value for submit button
            unset($_POST['addProduct']);
            // values array is parameter for Model method
            $values = [];
            // assign values to form variables and add values to values array
            foreach ($_POST as $key => $value) {
                $values[] = ${$key} = trim($value);
            }
            
            $values[] = $userID;

            // validate data in post
            $errors = $this->validate($_POST);
            //$errors=[];
           


            if (count($errors) == 0) {

                // no validation errors proceed to insert new booking
                $db = new EstoreDB();
                $success = $db->addProduct($values);
                $db->close();

                if ($success) {
                    header('location:?action=getProducts');
                }
            } else {
                include_once('view/addProductForm.php');
            }
        } else {  // load add form
            include_once('view/addProductForm.php');
        }
    }


   /**
     * edit booking function manages data model
     * and view for editing an existing booking
     * record
     */

    function editProduct() {
        $path = '../';
        $Description = $Category = $Quantity = $CostPrice = $SellingPrice = '';

        $ProductID = $_GET['ProductID'];

        if (isset($_POST['editProduct'])) {

            // remove ProductID and submit button key/value pairs from post
            unset($_POST['editProduct']);
            unset($_POST['ProductID']);
            $values = [];
            foreach ($_POST as $key => $value) {
                $value = trim($value);
                ${$key} = $value;
                $values[] = $value;
            }
            $errors = $this->validate($_POST);
            //$errors =[];

            if (count($errors) == 0) {

                $db = new EstoreDB();
                $success = $db->editProduct($values, $ProductID);
                $db->close();

                if ($success) {

                    header('location:?action=getProducts');
                }
            } else {
                include_once('view/editProductForm.php');
            }
        } else {

            // declare record 
            $record = [];
            $db = new EstoreDB();
            $record = $db->getProduct($ProductID);
            $db->close();

            foreach ($record as $key => $value) {
                ${$key} = $value;
            }

            // display populated Product form
            include_once('view/editProductForm.php');
        }
    }
    /**
     * searchBookings function manages data model
     * and view for searching booking records
     * record
     */
    function searchProducts() {
        $path = '../';
        $userID = $_SESSION['userID'];

        if (isset($_POST['searchProducts'])) {

            $keyword = $_POST['keyword'];

            $db = new EstoreDB();
            $records = $db->searchProducts($keyword,$userID);
            $db->close();
            $numRecords = count($records);

            // $records and $numRecords is referenced in viewProducts
            include_once('view/viewProducts.php');
        } else {
            include_once('view/searchForm.php');
        }
    }

    /**
     * getProducts function manages data model
     * and view for retrieving all the booking
     * records
     */
    function getProducts() {
        $userID = $_SESSION['userID'];
        $path = '../';
        $db = new EstoreDB();
        $records = $db->getProducts($userID);
        $db->close();
        $numRecords = count($records);
        include_once('view/viewProducts.php');
    }
    /**
     * validate function 
     * validates Bookings form data in $_POST array
     * @return $errors array
     */
    private function validate($post) {
        $errors = [];
        $fields = [
            
            ['name' => 'Description', 'valid_type' => 'alpha_num_spaces', 'required' => true],
            ['name' => 'Category', 'valid_type' => 'alpha_spaces', 'required' => true],
            ['name' => 'Quantity', 'valid_type' => 'digits', 'required' => true],
            ['name' => 'CostPrice', 'valid_type' => 'decimal', 'required' => true],
            ['name' => 'SellingPrice', 'valid_type' => 'decimal', 'required' => true]
        ];

        $validation = new Validation();
        $errors = $validation->validate_form_data($fields, $post);

        return $errors;
    }

    /**
     * validateInvitee function validates
     * Invitee form data in $_POST array
     */
    private function validatePhoto($post) {
        $errors = [];
        $fields = [
            ['name' => 'PhotoDescription', 'valid_type' => 'alpha_spaces', 'required' => true],
        ];
        $validation = new Validation();
        $errors = $validation->validate_form_data($fields, $_POST);

        return $errors;
    }

    /**
     * loads management home view
     */
    function management() {
        $path = '../';
        include_once('view/management.php');
    }

     /**
     * viewInvitess function manages data model
     * and view for retrieving all the Photos
     * for bookingID
     * records
     */
    function viewPhotos() {

        $path = '../';
        $ProductID = $_GET['ProductID'];
        $db = new EstoreDB();
        $records = $db->getPhotos($ProductID);
        $db->close();
        include_once('view/viewPhotos.php');
    }
     /**
     * addPhotos function manages data model
     * and view for adding a new Photo
     * record
     */
    function addPhoto() {
        $path = '../';
        $ProductID = $_GET['ProductID'];
        if (isset($_POST['addPhoto'])) {

            unset($_POST['addPhoto']);

            $values = [];
            foreach ($_POST as $key => $value) {
                $value = trim($value);
                ${$key} = $value;
            }


            $errors = $this->validatePhoto($_POST);
            $error_messages = [
                "Upload successful",
                "File exceeds maximum upload size specified by default",
                "File exceeds size specified by MAX_FILE_SIZE",
                "File only partially uploaded",
                "Form submitted with no file specified",
                "",
                "No temporary folder",
                "Cannot write file to disk",
                "File type is not permitted"
            ];

            $permitted = ["image/gif", "image/jpg", "image/jpeg", "image/png"];
            // how to retrieve the filename of the image
            $filename = $_FILES["image"]["name"];
            // remove spaces from $filename
            $filename = str_replace(" ", "", $filename);
            // how to retrieve the temporary filename path
            $temp_file = $_FILES["image"]["tmp_name"];
            // how to retrieve the file type (MIME)
            $type = $_FILES["image"]["type"];
            // how to retrieve error value if there is an error
            $errorLevel = $_FILES["image"]["error"];

            // define the upload directory destination
            $destination = $path.'photos/';
            $target_file = $destination . $filename;

            if ($errorLevel > 0) {
                // Set the error message to the errors array
                $errors['image'] = $error_messages[$errorLevel];
                include_once('view/addPhotoForm.php');
            } else {

                if (in_array($type, $permitted)) {

                    // add the values to an array
                    $values = [$filename, $PhotoDescription, $ProductID];
                  

                    if (count($errors) == 0) {
                        move_uploaded_file($temp_file, $target_file);
                        $db = new EstoreDB();
                        $success = $db->addPhoto($values);
                        $db->close();
                        // redirect user to manage bookings page
                        header('location:?action=getProducts');
                    } else {
                        include_once('view/addPhotoForm.php');
                    }
                } else {
                    $errors['image'] = "$filename type is not permitted";
                    include_once('view/addPhotoForm.php');
                }
            }  // end if file error
        } else { // No then display the addRecord form
            include_once('view/addPhotoForm.php');
        }
    }

// end addPhoto
} // end class
