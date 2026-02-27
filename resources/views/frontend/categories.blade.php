@extends('frontend.layouts.app')

@push('css')
    <style>
        .categories-header {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 40px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        .categories-header h1 {
            font-size: 36px;
            color: #203A72;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .breadcrumb-section {
            background-color: #EEEEEE;
            padding: 20px 0;
            margin-bottom: 0;
        }

        .breadcrumb-section .breadcrumb {
            margin-bottom: 0;
        }

        .category-filters {
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 50px 12px 20px;
            border: 1px solid #D9D9D9;
            border-radius: 30px;
            font-size: 18px;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            border-color: #203A72;
            box-shadow: 0 0 0 3px rgba(32, 58, 114, 0.1);
        }

        .search-box .search-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #203A72;
            font-size: 20px;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .category-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(32, 58, 114, 0.15);
        }

        .category-image-wrapper {
            position: relative;
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #F5FAFF 0%, #E8F4FF 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .category-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .category-card:hover .category-image-wrapper img {
            transform: scale(1.1);
        }

        .category-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: #203A72;
            color: #fff;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .category-badge i {
            font-size: 12px;
        }

        .category-content {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .category-name {
            font-weight: 600;
            color: #203A72;
            line-height: 1.3;
            min-height: 56px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .category-description {
            font-size: 16px;
            color: #666;
            line-height: 1.5;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .category-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #EEEEEE;
        }

        .category-products-count {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #9CADC0;
            font-size: 16px;
            font-weight: 500;
        }

        .category-products-count i {
            color: #203A72;
            font-size: 18px;
        }

        .category-arrow {
            width: 40px;
            height: 40px;
            background-color: #203A72;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            transition: all 0.3s ease;
        }

        .category-card:hover .category-arrow {
            transform: translateX(5px);
            background-color: #1a2d5a;
        }

        .category-arrow i {
            font-size: 18px;
        }

        .parent-category-info {
            background-color: #F5FAFF;
            padding: 20px 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .parent-category-info .back-link {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #203A72;
            font-size: 18px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .parent-category-info .back-link:hover {
            color: #1a2d5a;
            transform: translateX(-5px);
        }

        .parent-category-info .back-link i {
            font-size: 20px;
        }

        .parent-category-info .parent-name {
            font-size: 20px;
            color: #203A72;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        .empty-state-icon {
            width: 120px;
            height: 120px;
            background-color: #F5FAFF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }

        .empty-state-icon i {
            font-size: 60px;
            color: #9CADC0;
        }

        .empty-state h3 {
            font-size: 28px;
            color: #203A72;
            margin-bottom: 15px;
        }

        .empty-state p {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }

        .pagination-wrapper {
            display: flex;
            justify-content: center;
            padding: 40px 0;
        }

        .subcategories-indicator {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #203A72;
            font-size: 14px;
            margin-top: 8px;
        }

        .subcategories-indicator i {
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .category-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
            }

            .categories-header h1 {
                font-size: 28px;
            }

            .category-name {
                font-size: 20px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="categories-page">
        <div class="breadcrumb-section">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Categories</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container">

            @if($parentCategory)
                <div class="parent-category-info">
                    <a href="{{ route('categories') }}" class="back-link">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to All Categories</span>
                    </a>
                    <div class="parent-name">
                        <i class="bi bi-folder-fill me-2"></i>
                        {{ $parentCategory->name }}
                    </div>
                </div>
            @endif

            @if($categories->count() > 0)
                <div class="category-grid mt-4">
                    @foreach($categories as $category)
                        <div class="category-card"
                            onclick="window.location.href='{{ route('products', ['category' => $category->id]) }}'">
                            <div class="category-image-wrapper">
                                <img src="{{ $category->logo_url }}" alt="{{ $category->name }}"
                                    onerror="this.src='{{ asset('no-image-found.jpg') }}'">
                                @if($category->products_count > 0)
                                    <div class="category-badge">
                                        <i class="bi bi-box-seam"></i>
                                        {{ $category->products_count }} {{ $category->products_count == 1 ? 'Product' : 'Products' }}
                                    </div>
                                @endif
                            </div>
                            <div class="category-content">
                                <h3 class="category-name">{{ $category->name }}</h3>
                                @if($category->description)
                                    <p class="category-description">{{ $category->description }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($categories->hasPages())
                    <div class="pagination-wrapper">
                        <nav aria-label="Category pagination">
                            <ul class="pagination justify-content-center custom-pagination">
                                {{-- Previous Page Link --}}
                                @if($categories->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            <i class="bi bi-chevron-left"></i>
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $categories->previousPageUrl() }}" aria-label="Previous">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                                    @if($page == $categories->currentPage())
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if($categories->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $categories->nextPageUrl() }}" aria-label="Next">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            <i class="bi bi-chevron-right"></i>
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <h3>No Categories Found</h3>
                    <p>
                        @if($search)
                            We couldn't find any categories matching "{{ $search }}". Try a different search term.
                        @else
                            There are no categories available at the moment. Please check back later.
                        @endif
                    </p>
                    @if($search)
                        <a href="{{ route('categories') }}" class="btn cart-btn">
                            <i class="bi bi-arrow-left me-2"></i>Clear Search
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

{{-- @push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('categoryFilterForm').submit();
                }
            });
        }

        const paginationLinks = document.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function () {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    });
</script>
@endpush --}}