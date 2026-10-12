<?php

require_once __DIR__ . '/../core/db_class.php';

class CustomerClass extends Database
{
    
    public function emailExists($email)
    {
        $stmt = $this->conn->prepare(
            "SELECT customer_email 
             FROM customer 
             WHERE customer_email = ? 
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        $exists = $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }


    
    public function addCustomer(
        $name,
        $email,
        $pass,
        $country,
        $city,
        $contact
    ) {
        
        $hash = password_hash($pass, PASSWORD_BCRYPT);

        $stmt = $this->conn->prepare(
            "INSERT INTO customer
            (
                customer_name,
                customer_email,
                customer_pass,
                customer_country,
                customer_city,
                customer_contact
            )
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssssss",
            $name,
            $email,
            $hash,
            $country,
            $city,
            $contact
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }



    public function getCustomerByEmail($email)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM customer
             WHERE customer_email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        $customer = $result->fetch_assoc();

        $stmt->close();

        if ($customer) {
            return $customer;
        }

        return false;
    }

    public function getCustomerById($customerId)
    {
        $stmt = $this->conn->prepare(
            'SELECT
                customer_id,
                customer_name,
                customer_email,
                customer_country,
                customer_city,
                customer_contact,
                customer_address,
                customer_image,
                user_role
             FROM customer
             WHERE customer_id = ?
             LIMIT 1'
        );
        $stmt->bind_param('i', $customerId);
        $stmt->execute();
        $customer = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $customer ?: false;
    }

    public function updateCustomerProfile(
        $customerId,
        $country,
        $city,
        $contact,
        $address,
        $imagePath
    ) {
        $stmt = $this->conn->prepare(
            'UPDATE customer
             SET customer_country = ?,
                 customer_city = ?,
                 customer_contact = ?,
                 customer_address = ?,
                 customer_image = ?
             WHERE customer_id = ?'
        );
        $stmt->bind_param(
            'sssssi',
            $country,
            $city,
            $contact,
            $address,
            $imagePath,
            $customerId
        );
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function getAllCustomers()
    {
        $result = $this->conn->query(
            'SELECT
                customer_id,
                customer_name,
                customer_email,
                customer_country,
                customer_city,
                customer_contact,
                user_role
             FROM customer
             ORDER BY customer_id DESC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }


    
    public function login($email, $pass)
    {
        $customer = $this->getCustomerByEmail($email);

        
        if ($customer === false) {
            return false;
        }

    
        if (!password_verify($pass, $customer['customer_pass'])) {
            return false;
        }

        return $customer;
    }
}

?>