<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        // Card grid by default; ?view=list shows a table
        $view = $request->query('view') === 'list' ? 'list' : 'cards';

        return view('admin.pages.services.index', [
            'services' => Service::ordered()->withCount('exams')->get(),
            'view' => $view,
        ]);
    }

    public function create()
    {
        return view('admin.pages.services.form', ['service' => new Service([
            'icon' => Service::ICONS[0],
            'is_active' => true,
            'requires_application_form' => true,
        ])]);
    }

    public function store(ServiceRequest $request)
    {
        Service::create($request->serviceData());

        return redirect()->route('services.index')->with('success', __('Service created.'));
    }

    public function edit(Service $service)
    {
        return view('admin.pages.services.form', ['service' => $service]);
    }

    public function update(ServiceRequest $request, Service $service)
    {
        $service->update($request->serviceData());

        return redirect()->route('services.index')->with('success', __('Service updated.'));
    }

    public function destroy(Service $service)
    {
        if ($service->exams()->exists()) {
            return back()->with('error', __('This service has exams. Delete its exams first, or mark the service inactive.'));
        }

        $service->delete();

        return redirect()->route('services.index')->with('success', __('Service deleted.'));
    }
}
