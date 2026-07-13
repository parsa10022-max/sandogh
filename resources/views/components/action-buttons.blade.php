<div class="d-flex justify-content-center gap-1">

    {{-- مشاهده --}}
    @isset($showRoute)
        <a href="{{ $showRoute }}"
           class="btn btn-sm btn-outline-info"
           title="مشاهده">

            <i class="bi bi-eye"></i>

        </a>
    @endisset


    {{-- ویرایش --}}
    @isset($editRoute)
        <a href="{{ $editRoute }}"
           class="btn btn-sm btn-outline-primary"
           title="ویرایش">

            <i class="bi bi-pencil-square"></i>

        </a>
    @endisset


    {{-- فعال / غیرفعال --}}
    @isset($changeStatusRoute)
        <form action="{{ $changeStatusRoute }}"
              method="POST">

            @csrf
            @method('PATCH')

            <button type="submit"
                    class="btn btn-sm btn-outline-warning"
                    title="تغییر وضعیت">

                <i class="bi bi-arrow-repeat"></i>

            </button>

        </form>
    @endisset


    {{-- حذف --}}
    @isset($deleteRoute)
        <form action="{{ $deleteRoute }}"
              method="POST"
              onsubmit="return confirm('آیا از حذف این رکورد مطمئن هستید؟')">

            @csrf
            @method('DELETE')

            <button type="submit"
                    class="btn btn-sm btn-outline-danger"
                    title="حذف">

                <i class="bi bi-trash"></i>

            </button>

        </form>
    @endisset

</div>
