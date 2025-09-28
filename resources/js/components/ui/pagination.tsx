import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { ChevronLeft, ChevronRight } from 'lucide-react';

interface PaginationProps {
  currentPage: number;
  totalPages: number;
  onPageChange: (page: number) => void;
  showSummary?: boolean;
  totalItems?: number;
  itemsPerPage?: number;
  className?: string;
}

export function Pagination({
  currentPage,
  totalPages,
  onPageChange,
  showSummary = false,
  totalItems,
  itemsPerPage,
  className = "",
}: PaginationProps) {
  if (totalPages <= 1) {
    return null;
  }

  const startIndex = (currentPage - 1) * (itemsPerPage || 10) + 1;
  const endIndex = Math.min(currentPage * (itemsPerPage || 10), totalItems || totalPages * (itemsPerPage || 10));

  return (
    <Card className={className}>
      <CardContent>
        <div className="flex items-center justify-between">
          {showSummary && totalItems && (
            <div className="flex items-center gap-2">
              <p className="text-sm text-muted-foreground">
                Showing {endIndex} of {totalItems} items -{totalPages > 1 && ` Page ${currentPage} of ${totalPages}`}
              </p>
            </div>
          )}
          
          <div className="flex items-center gap-2 ml-auto">
            <Button
              variant="outline"
              size="sm"
              onClick={() => onPageChange(currentPage - 1)}
              disabled={currentPage === 1}
              className="flex items-center gap-1"
            >
              <ChevronLeft className="h-4 w-4" />
              Previous
            </Button>
            
            {/* Page Numbers */}
            <div className="flex items-center gap-1">
              {Array.from({ length: totalPages }, (_, i) => i + 1).map((page) => {
                // Show first page, last page, current page, and pages around current page
                const showPage = 
                  page === 1 || 
                  page === totalPages || 
                  Math.abs(page - currentPage) <= 1;
                
                if (!showPage) {
                  // Show ellipsis for gaps
                  if (page === 2 && currentPage > 4) {
                    return <span key={`ellipsis-start`} className="px-2 text-muted-foreground">...</span>;
                  }
                  if (page === totalPages - 1 && currentPage < totalPages - 3) {
                    return <span key={`ellipsis-end`} className="px-2 text-muted-foreground">...</span>;
                  }
                  return null;
                }
                
                return (
                  <Button
                    key={page}
                    variant={page === currentPage ? "default" : "outline"}
                    size="sm"
                    onClick={() => onPageChange(page)}
                    className="w-8 h-8 p-0"
                  >
                    {page}
                  </Button>
                );
              })}
            </div>
            
            <Button
              variant="outline"
              size="sm"
              onClick={() => onPageChange(currentPage + 1)}
              disabled={currentPage === totalPages}
              className="flex items-center gap-1"
            >
              Next
              <ChevronRight className="h-4 w-4" />
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
