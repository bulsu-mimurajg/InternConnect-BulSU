import { useState, useCallback, useMemo } from 'react';

interface UsePaginationProps<T> {
  data: T[];
  itemsPerPage?: number;
  initialPage?: number;
}

interface UsePaginationReturn<T> {
  currentPage: number;
  totalPages: number;
  paginatedData: T[];
  startIndex: number;
  endIndex: number;
  setCurrentPage: (page: number) => void;
  handlePageChange: (page: number) => void;
  resetToFirstPage: () => void;
}

export function usePagination<T>({
  data,
  itemsPerPage = 10,
  initialPage = 1,
}: UsePaginationProps<T>): UsePaginationReturn<T> {
  const [currentPage, setCurrentPage] = useState(initialPage);

  const totalPages = Math.ceil(data.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  
  const paginatedData = useMemo(() => {
    return data.slice(startIndex, endIndex);
  }, [data, startIndex, endIndex]);

  const handlePageChange = useCallback((page: number) => {
    setCurrentPage(page);
  }, []);

  const resetToFirstPage = useCallback(() => {
    setCurrentPage(1);
  }, []);

  return {
    currentPage,
    totalPages,
    paginatedData,
    startIndex,
    endIndex,
    setCurrentPage,
    handlePageChange,
    resetToFirstPage,
  };
}
