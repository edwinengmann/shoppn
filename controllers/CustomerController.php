<?php

require_once __DIR__ . '/../classes/CustomerClass.php';

class CustomerController
{
    private $customerModel;


    public function __construct()
    {
        $this->customerModel = new CustomerClass();
    }


    public function register($data)
    {
        
        if ($this->customerModel->emailExists($data['email'])) {

            return [
                'success' => false,
                'error' => 'Email already registered.'
            ];
        }


        $created = $this->customerModel->addCustomer(
            $data['name'],
            $data['email'],
            $data['pass'],
            $data['country'],
            $data['city'],
            $data['contact']
        );


        if (!$created) {

            return [
                'success' => false,
                'error' => 'Registration failed. Please try again.'
            ];
        }


        return [
            'success' => true
        ];
    }


    
    public function login($email, $pass)
    {
        $customer = $this->customerModel->login(
            $email,
            $pass
        );


        if ($customer === false) {

            return [
                'success' => false,
                'error' => 'Invalid email or password.'
            ];
        }


        return [
            'success' => true,
            'customer' => $customer
        ];
    }
}

?>