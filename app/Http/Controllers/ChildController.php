<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Http\Requests\Child\StoreChildRequest;
use App\Http\Requests\Child\UpdateChildRequest;
use App\Services\ChildService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ChildController extends Controller
{
    protected ChildService $childService;

    public function __construct(ChildService $childService)
    {
        $this->childService = $childService;
    }

    /**
     * Display a listing of the children.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $children = $this->childService->listChildren($search, $status);

        return Inertia::render('Children/Index', [
            'children' => $children,
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new child.
     */
    public function create(): Response
    {
        return Inertia::render('Children/Create');
    }

    /**
     * Store a newly created child in storage.
     */
    public function store(StoreChildRequest $request): RedirectResponse
    {
        $child = $this->childService->createChild($request->validated());

        return redirect()->route('children.show', $child->id)
            ->with('success', 'Đã tạo hồ sơ trẻ.');
    }

    /**
     * Display the specified child.
     */
    public function show(Child $child): Response
    {
        $detailedChild = $this->childService->getChildDetail($child);

        return Inertia::render('Children/Show', [
            'child' => $detailedChild,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified child.
     */
    public function edit(Child $child): Response
    {
        return Inertia::render('Children/Edit', [
            'child' => $child,
        ]);
    }

    /**
     * Update the specified child in storage.
     */
    public function update(UpdateChildRequest $request, Child $child): RedirectResponse
    {
        $this->childService->updateChild($child, $request->validated());

        return redirect()->route('children.show', $child->id)
            ->with('success', 'Đã cập nhật hồ sơ trẻ.');
    }

    /**
     * Remove the specified child from storage.
     * Only voided or stopped children can be deleted.
     */
    public function destroy(Child $child): RedirectResponse
    {
        if (!$this->childService->deleteChild($child)) {
            return redirect()->route('children.index')
                ->with('error', 'Chỉ hồ sơ đã ngừng/dừng can thiệp mới có thể xóa.');
        }

        return redirect()->route('children.index')
            ->with('success', 'Đã xóa hồ sơ trẻ.');
    }

    public function pause(Request $request, Child $child): RedirectResponse
    {
        $validated = $request->validate([
            'status_note' => ['nullable', 'string'],
        ]);

        $this->childService->pauseChild($child, $validated['status_note'] ?? null);

        return redirect()->back()->with('success', 'Đã chuyển trẻ sang trạng thái tạm nghỉ.');
    }

    public function activate(Child $child): RedirectResponse
    {
        $this->childService->activateChild($child);

        return redirect()->back()->with('success', 'Đã kích hoạt lại hồ sơ can thiệp.');
    }

    public function resume(Child $child): RedirectResponse
    {
        if (!$this->childService->resumeChild($child)) {
            return redirect()->back()->with('error', 'Chỉ trẻ đang tạm nghỉ hoặc dừng can thiệp mới có thể tiếp tục can thiệp.');
        }

        return redirect()->back()->with('success', 'Đã tiếp tục can thiệp cho trẻ.');
    }

    public function void(Request $request, Child $child): RedirectResponse
    {
        $validated = $request->validate([
            'status_note' => ['nullable', 'string'],
        ]);

        $this->childService->voidChild($child, $validated['status_note'] ?? null);

        return redirect()->route('children.index')->with('success', 'Đã ngừng can thiệp cho trẻ.');
    }
}
