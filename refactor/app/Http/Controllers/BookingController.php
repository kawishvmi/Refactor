<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
  public function index(Request $request)
{
    $user = $request->__authenticatedUser;
    $jobs = $this->repository->getJobsByUser($user, $request->get('user_id'));

    return response($jobs);
}



    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);

        return response($job);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
{
    $cuser = $request->__authenticatedUser;
    $data = $request->all();
    $response = $this->repository->create($cuser, $data);
    
    return response($response);
}


    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
{
    $data = $request->except(['_token', 'submit']);
    $user = $request->__authenticatedUser;

    $response = $this->repository->updateJob($id, $data, $user);

    return response($response);
}


    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $adminSenderEmail = config('app.adminemail');
        $data = $request->all();
        return response($this->repository->storeJobEmail($data));
    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if($user_id = $request->get('user_id')) {

            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            return response($response);
        }

        return null;
    }

    /**
     * @param Request $request
     * @return mixed
     */
   public function handleJob(Request $request, string $action)
{
    $data = $request->all();
    $user = $request->__authenticatedUser;

    switch ($action) {
        case 'accept':
            $response = $this->repository->acceptJob($data, $user);
            break;
        case 'acceptWithId':
            $data = $request->get('job_id');
            $response = $this->repository->acceptJobWithId($data, $user);
            break;
        case 'cancel':
            $response = $this->repository->cancelJobAjax($data, $user);
            break;
        case 'end':
            $response = $this->repository->endJob($data);
            break;
        default:
            return response(['error' => 'Invalid action']);
    }

    return response($response);
}


    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->customerNotCall($data);

        return response($response);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->getPotentialJobs($user);

        return response($response);
    }

public function distanceFeed(Request $request)
{
    $data = $request->all();
    $jobId = $request->input('jobid');
    $distance = $request->input('distance');
    $time = $request->input('time');
    $sessionTime = $request->input('session_time');
    $flagged = $request->input('flagged') === 'true' ? 'yes' : 'no';
    $manuallyHandled = $request->input('manually_handled') === 'true' ? 'yes' : 'no';
    $byAdmin = $request->input('by_admin') === 'true' ? 'yes' : 'no';
    $adminComment = $request->input('admincomment');

    $this->updateDistance($jobId, $distance, $time);
    $this->updateJob($jobId, $adminComment, $sessionTime, $flagged, $manuallyHandled, $byAdmin);

    return response('Record updated!');
}

private function updateDistance($jobId, $distance, $time)
{
    if ($time || $distance) {
        Distance::where('job_id', $jobId)->update(['distance' => $distance, 'time' => $time]);
    }
}

private function updateJob($jobId, $adminComment, $sessionTime, $flagged, $manuallyHandled, $byAdmin)
{
    if ($flagged === 'yes' && $adminComment === '') {
        return response("Please, add comment");
    }

    if ($adminComment || $sessionTime || $flagged || $manuallyHandled || $byAdmin) {
        Job::where('id', $jobId)->update([
            'admin_comments' => $adminComment,
            'flagged' => $flagged,
            'session_time' => $sessionTime,
            'manually_handled' => $manuallyHandled,
            'by_admin' => $byAdmin,
        ]);
    }
}



    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $job = $this->repository->find($request->input('jobid'));
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }


    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
 public function resendSMSNotifications(Request $request)
{
    $job = $this->repository->find($request->input('jobid'));
    try {
        $this->repository->sendSMSNotificationToTranslator($job);
        return response(['success' => 'SMS sent']);
    } catch (\Exception $e) {
        return response(['success' => $e->getMessage()]);
    }
}


}
