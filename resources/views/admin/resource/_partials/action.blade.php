@if($resource->hasAction('edit') && laradium()->hasPermissionTo(auth()->user(), $resource, 'edit'))
    <a href="/admin/{{ $slug }}/{{ $item->id }}/edit" class="btn btn-primary btn-xs"><i class="mdi mdi-pencil"></i> Edit</a>
@endif

@if($resource->hasAction('delete') && laradium()->hasPermissionTo(auth()->user(), $resource, 'destroy'))
    <a href="javascript:;"
       data-url="/admin/{{ $slug }}/{{ $item->id }}"
       class="btn btn-danger btn-xs js-delete-resource">
        <i class="mdi mdi-delete"></i> Delete
    </a>
@endif