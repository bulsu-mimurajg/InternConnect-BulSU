/**
 * Utility functions for pagination
 */

/**
 * Calculate row numbers for paginated data
 * @param currentPage Current page number (1-based)
 * @param itemsPerPage Number of items per page
 * @param totalItems Total number of items
 * @returns Array of row numbers for the current page
 */
export function calculateRowNumbers(
  currentPage: number,
  itemsPerPage: number,
  totalItems: number
): number[] {
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
  
  return Array.from({ length: endIndex - startIndex }, (_, index) => startIndex + index + 1);
}

/**
 * Get row number for a specific item in paginated data
 * @param currentPage Current page number (1-based)
 * @param itemsPerPage Number of items per page
 * @param itemIndex Index of the item in the current page (0-based)
 * @returns Row number (1-based)
 */
export function getRowNumber(
  currentPage: number,
  itemsPerPage: number,
  itemIndex: number
): number {
  return (currentPage - 1) * itemsPerPage + itemIndex + 1;
}
