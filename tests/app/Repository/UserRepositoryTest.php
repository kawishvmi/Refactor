<?php

use PHPUnit\Framework\TestCase;
use App\Repository\UserRepository;
use App\Models\User;
/*

This test case creates a new instance of the User model with some data, then passes it to the createOrUpdate method of the UserRepository class. The expected result of the method is true, indicating that the user was successfully saved. The test case asserts this result using the assertTrue method.

*/
class UserRepositoryTest extends TestCase
{
    public function testCreateOrUpdate()
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'John Doe';
        $user->email = 'john.doe@example.com';

        $repository = new UserRepository();
        $result = $repository->createOrUpdate($user);

        $this->assertTrue($result);
    }
}

/* try to write in detial*/

class UserRepositoryTest extends TestCase
{
    private $repository;
    private $model;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository(new User());
        $this->model = new User();
    }

    public function testCreateOrUpdate()
    {
        // Create request data for testing
        $requestData = [
            'role' => Role::where('name', 'customer')->first()->id,
            'name' => 'Test User',
            'email' => 'testuser@test.com',
            'dob_or_orgid' => '123456789',
            'phone' => '0123456789',
            'mobile' => '9876543210',
            'password' => 'password',
            'consumer_type' => 'paid',
            'customer_type' => 'business',
            'username' => 'testuser',
            'post_code' => '123456',
            'address' => 'Test Address',
            'city' => 'Test City',
            'town' => 'Test Town',
            'country' => 'Test Country',
            'reference' => 'yes',
            'additional_info' => 'Test Additional Info',
            'cost_place' => 'Test Cost Place',
            'fee' => '10.00',
            'time_to_charge' => '2022-03-01',
            'time_to_pay' => '2022-03-05',
            'charge_ob' => '10.00',
            'customer_id' => 'Test Customer ID',
            'charge_km' => '10.00',
            'maximum_km' => '100.00',
        ];

        // Test for create
        $response = $this->repository->createOrUpdate(null, $requestData);

        $createdUser = User::where('email', 'testuser@test.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals($requestData['name'], $createdUser->name);
        $this->assertEquals($requestData['email'], $createdUser->email);
        $this->assertEquals($requestData['dob_or_orgid'], $createdUser->dob_or_orgid);
        $this->assertEquals($requestData['phone'], $createdUser->phone);
        $this->assertEquals($requestData['mobile'], $createdUser->mobile);
        $this->assertTrue(Hash::check($requestData['password'], $createdUser->password));
        $this->assertEquals($requestData['role'], $createdUser->user_type);

        $createdUserMeta = UserMeta::where('user_id', $createdUser->id)->first();

        if(!$createdUserMeta){
        $createdUserMeta = new UserMeta();
        $createdUserMeta->user_id = $createdUser->id;
        }

        $createdUserMeta->phone = $request->input('phone');
        $createdUserMeta->address = $request->input('address');

        $createdUserMeta->save();

        return response()->json(['success' => true, 'user' => $createdUser]);
        }
        catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
}
