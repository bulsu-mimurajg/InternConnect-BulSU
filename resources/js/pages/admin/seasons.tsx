import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { CalendarIcon, PlusIcon, CheckCircleIcon, ClockIcon, UsersIcon, CalendarDaysIcon, ArrowLeftIcon, ArchiveIcon } from 'lucide-react';
import { format } from 'date-fns';
import { cn } from '@/lib/utils';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import AdminLayout from '@/layouts/admin/layout';

interface InternshipSeason {
  id: number;
  name: string;
  start_date: string;
  end_date: string;
  status: 'active' | 'inactive' | 'completed' | 'archived';
  created_at: string;
  updated_at: string;
  deadlines_count: number;
  students_count: number;
  active_students_count: number;
  // New fields for automatic status management
  automatic_status?: string;
  status_transition_reason?: string;
  needs_attention?: boolean;
  // Deadline statuses for each category
  deadline_statuses?: {
    hte_assessment_form: string;
    student_verification: string;
    student_assessment_form: string;
    internship_placement: string;
    archive_students: string;
  };
}

interface Props {
  seasons: InternshipSeason[];
  activeSeason: InternshipSeason | null;
  activeSeasonDeadlines?: {
    all: any[];
    active: any[];
    expired: any[];
  } | null;
}

export default function SeasonsManagement({ seasons, activeSeason, activeSeasonDeadlines }: Props) {
  const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [deactivateDialog, setDeactivateDialog] = useState<{
    open: boolean;
    season: InternshipSeason | null;
  }>({ open: false, season: null });
  const [confirmationText, setConfirmationText] = useState('');
  const [formData, setFormData] = useState({
    name: '',
    start_date: '',
    end_date: '',
  });

  const handleCreateSeason = (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);

    router.post('/admin/seasons', formData, {
      onSuccess: () => {
        setIsCreateDialogOpen(false);
        setFormData({ name: '', start_date: '', end_date: '' });
      },
      onFinish: () => setIsLoading(false),
    });
  };

  const handleActivateSeason = (season: InternshipSeason) => {
    if (activeSeason) {
      alert(`Season "${activeSeason.name}" is currently active. Please deactivate it first.`);
      return;
    }
    
    // Check if all 5 categories exist
    if (season.deadlines_count < 5) {
      alert('All 5 deadline categories must be created before activating this season.');
      return;
    }
    
    setIsLoading(true);
    router.post(`/admin/seasons/${season.id}/activate`, {}, {
      onFinish: () => setIsLoading(false),
    });
  };


  const handleDeactivateSeason = (season: InternshipSeason) => {
    setDeactivateDialog({ open: true, season });
    setConfirmationText('');
  };

  const confirmDeactivate = () => {
    if (confirmationText !== 'I understand') {
      alert('Please type "I understand" to confirm.');
      return;
    }
    
    if (deactivateDialog.season) {
      setIsLoading(true);
      router.post(`/admin/seasons/${deactivateDialog.season.id}/deactivate`, {
        confirmation: confirmationText
      }, {
        onSuccess: () => {
          setDeactivateDialog({ open: false, season: null });
          setConfirmationText('');
        },
        onFinish: () => setIsLoading(false),
      });
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'active':
        return <Badge className="bg-green-100 text-green-800">Active</Badge>;
      case 'inactive':
        return <Badge className="bg-gray-100 text-gray-800">Inactive</Badge>;
      case 'completed':
        return <Badge className="bg-blue-100 text-blue-800">Completed</Badge>;
      case 'archived':
        return <Badge className="bg-gray-100 text-gray-800">Archived</Badge>;
      default:
        return <Badge variant="secondary">{status}</Badge>;
    }
  };

  const formatDate = (dateString: string) => {
    return format(new Date(dateString), 'MMM dd, yyyy');
  };

  return (
    <AdminLayout>
      <Head title="Internship Seasons" />
      
      <div className="p-4 md:p-6 space-y-6">
        {/* Back Button */}
        <div className="flex items-center">
          <Button
            variant="outline"
            size="sm"
            onClick={() => router.get('/admin/events')}
            className="flex items-center gap-2"
          >
            <ArrowLeftIcon className="h-4 w-4" />
            Back to Events
          </Button>
        </div>
        
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 className="text-2xl font-bold text-foreground">Internship Seasons</h1>
            <p className="text-muted-foreground">Manage internship seasons and student archiving.</p>
          </div>
          
          <div className="flex items-center gap-2">
            <Dialog open={isCreateDialogOpen} onOpenChange={setIsCreateDialogOpen}>
              <DialogTrigger asChild>
                <Button className="flex items-center gap-2">
                  <PlusIcon className="h-4 w-4" />
                  Create Season
                </Button>
              </DialogTrigger>
            <DialogContent className="sm:max-w-md">
              <DialogHeader>
                <DialogTitle>Create New Internship Season</DialogTitle>
                <DialogDescription>
                  Create a new internship season to organize deadlines and manage student archiving.
                </DialogDescription>
              </DialogHeader>
              
              <form onSubmit={handleCreateSeason} className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="name">Season Name</Label>
                  <Input
                    id="name"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    placeholder="e.g., AY 2024-2025 First Semester"
                    required
                  />
                </div>
                
                <div className="space-y-2">
                  <Label htmlFor="start_date">Start Date</Label>
                  <Input
                    id="start_date"
                    type="datetime-local"
                    value={formData.start_date}
                    onChange={(e) => setFormData({ ...formData, start_date: e.target.value })}
                    required
                  />
                </div>
                
                <div className="space-y-2">
                  <Label htmlFor="end_date">End Date</Label>
                  <Input
                    id="end_date"
                    type="datetime-local"
                    value={formData.end_date}
                    onChange={(e) => setFormData({ ...formData, end_date: e.target.value })}
                    required
                  />
                </div>
                
                <div className="flex justify-end gap-2">
                  <Button
                    type="button"
                    variant="outline"
                    onClick={() => setIsCreateDialogOpen(false)}
                  >
                    Cancel
                  </Button>
                  <Button type="submit" disabled={isLoading}>
                    {isLoading ? 'Creating...' : 'Create Season'}
                  </Button>
                </div>
              </form>
            </DialogContent>
          </Dialog>
          </div>
        </div>

        {/* Deactivation Confirmation Dialog */}
        <Dialog open={deactivateDialog.open} onOpenChange={(open) => {
          if (!open) {
            setDeactivateDialog({ open: false, season: null });
            setConfirmationText('');
          }
        }}>
          <DialogContent className="sm:max-w-md">
            <DialogHeader>
              <DialogTitle>Deactivate Season</DialogTitle>
              <DialogDescription>
                This will mark the season as completed and expire all active deadlines. 
                This action cannot be undone.
              </DialogDescription>
            </DialogHeader>
            
            <div className="space-y-4">
              <div className="bg-red-50 border border-red-200 rounded-md p-4">
                <p className="text-sm text-red-800 font-medium">
                  Warning: This action is permanent
                </p>
                <ul className="text-sm text-red-700 mt-2 space-y-1 list-disc list-inside">
                  <li>All active deadlines will be expired</li>
                  <li>Season will be marked as completed</li>
                  <li>This cannot be undone</li>
                </ul>
              </div>
              
              <div className="space-y-2">
                <Label htmlFor="confirmation">
                  Type "I understand" to confirm
                </Label>
                <Input
                  id="confirmation"
                  value={confirmationText}
                  onChange={(e) => setConfirmationText(e.target.value)}
                  placeholder="I understand"
                />
              </div>
              
              <div className="flex justify-end gap-2">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => {
                    setDeactivateDialog({ open: false, season: null });
                    setConfirmationText('');
                  }}
                >
                  Cancel
                </Button>
                <Button
                  type="button"
                  variant="destructive"
                  onClick={confirmDeactivate}
                  disabled={confirmationText !== 'I understand'}
                >
                  Deactivate Season
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        {/* Active Season Info */}
        {activeSeason && (
          <Card className="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950/50">
            <CardHeader className="pb-4">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <CheckCircleIcon className="h-5 w-5 text-green-600 dark:text-green-400" />
                  </div>
                  <div>
                    <h3 className="font-semibold text-green-900 dark:text-green-100">Current Active Season</h3>
                    <p className="text-sm text-green-700 dark:text-green-300">{activeSeason.name}</p>
                  </div>
                </div>
                <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                  Active
                </Badge>
              </div>
            </CardHeader>
            <CardContent className="pt-0">
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div className="flex items-start gap-3">
                  <CalendarIcon className="h-4 w-4 text-green-600 dark:text-green-400 mt-0.5" />
                  <div className="space-y-1">
                    <p className="text-sm font-medium text-green-800 dark:text-green-200">Duration</p>
                    <p className="text-sm text-green-700 dark:text-green-300">
                      {formatDate(activeSeason.start_date)} - {formatDate(activeSeason.end_date)}
                    </p>
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <ClockIcon className="h-4 w-4 text-green-600 dark:text-green-400 mt-0.5" />
                  <div className="space-y-1">
                    <p className="text-sm font-medium text-green-800 dark:text-green-200">Deadlines</p>
                    <p className="text-sm text-green-700 dark:text-green-300">
                      {activeSeasonDeadlines ? 
                        `${activeSeasonDeadlines.all.length} total (${activeSeasonDeadlines.active.length} active, ${activeSeasonDeadlines.expired.length} expired)` :
                        `${activeSeason.deadlines_count} total deadlines`
                      }
                    </p>
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <UsersIcon className="h-4 w-4 text-green-600 dark:text-green-400 mt-0.5" />
                  <div className="space-y-1">
                    <p className="text-sm font-medium text-green-800 dark:text-green-200">Students</p>
                    <p className="text-sm text-green-700 dark:text-green-300">
                      {activeSeason.active_students_count} active ({activeSeason.students_count} total)
                    </p>
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <CheckCircleIcon className="h-4 w-4 text-green-600 dark:text-green-400 mt-0.5" />
                  <div className="space-y-1">
                    <p className="text-sm font-medium text-green-800 dark:text-green-200">Placements</p>
                    <p className="text-sm text-green-700 dark:text-green-300">
                      {(() => {
                        const placedCount = activeSeason.students_count - activeSeason.active_students_count;
                        const placementRate = activeSeason.students_count > 0 ? Math.round((placedCount / activeSeason.students_count) * 100) : 0;
                        return `${placedCount} placed (${placementRate}%)`;
                      })()}
                    </p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Seasons List */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {seasons.map((season) => (
            <Card key={season.id} className="relative flex flex-col h-full">
              <CardHeader>
                <div className="space-y-2">
                  <CardTitle className="text-lg">{season.name}</CardTitle>
                  <div className="flex items-center justify-between">
                    <CardDescription>
                      {formatDate(season.start_date)} - {formatDate(season.end_date)}
                    </CardDescription>
                    {getStatusBadge(season.status)}
                  </div>
                </div>
              </CardHeader>
              
              <CardContent className="flex flex-col h-full">
                <div className="flex-1 space-y-4">
                  {/* Statistics */}
                  <div className="flex items-center justify-between text-sm">
                    <div className="flex items-center gap-2">
                      <CalendarDaysIcon className="h-4 w-4 text-muted-foreground" />
                      <span>{season.deadlines_count} deadlines</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <UsersIcon className="h-4 w-4 text-muted-foreground" />
                      <span>{season.students_count} students</span>
                    </div>
                  </div>

                  {/* Deadline Requirements */}
                  {season.status === 'inactive' && (
                    <div className="space-y-2">
                      <div className="flex items-center justify-between">
                        <div className="text-sm font-medium text-foreground">Deadline Requirements:</div>
                        <div className="text-xs text-muted-foreground">
                          {season.deadlines_count}/5 completed
                        </div>
                      </div>
                      
                      {/* Progress Bar */}
                      <div className="w-full bg-muted rounded-full h-2">
                        <div 
                          className="bg-green-500 h-2 rounded-full transition-all duration-300" 
                          style={{ width: `${(season.deadlines_count / 5) * 100}%` }}
                        />
                      </div>
                      
                      <div className="grid grid-cols-1 gap-1 text-xs">
                        {[
                          { key: 'hte_assessment_form', label: 'HTE Assessment Form' },
                          { key: 'student_verification', label: 'Student Verification' },
                          { key: 'student_assessment_form', label: 'Student Assessment Form' },
                          { key: 'internship_placement', label: 'Internship Placement' },
                          { key: 'archive_students', label: 'Archive Students' }
                        ].map((category) => (
                          <div key={category.key} className="flex items-center gap-2">
                            <div className={`w-2 h-2 rounded-full ${
                              season.deadlines_count >= 5 ? 'bg-green-500' : 'bg-muted-foreground/50'
                            }`} />
                            <span className={`${
                              season.deadlines_count >= 5 ? 'text-green-700 dark:text-green-300' : 'text-muted-foreground'
                            }`}>
                              {category.label}
                            </span>
                          </div>
                        ))}
                      </div>
                      
                      {season.deadlines_count < 5 && (
                        <div className="text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/50 p-2 rounded border border-amber-200 dark:border-amber-800">
                          <div className="flex items-center gap-1 mb-1">
                            <ClockIcon className="h-3 w-3" />
                            <strong>Activation Pending</strong>
                          </div>
                          <div className="mb-2">
                            {5 - season.deadlines_count} more deadline{5 - season.deadlines_count !== 1 ? 's' : ''} required to activate this season.
                          </div>
                          <div>
                            <Button
                              size="sm"
                              variant="outline"
                              onClick={() => router.get('/admin/events')}
                              className="text-xs h-6 px-2"
                            >
                              Create Deadlines
                            </Button>
                          </div>
                        </div>
                      )}
                      
                      {season.deadlines_count >= 5 && (
                        <div className="text-xs text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-950/50 p-2 rounded border border-green-200 dark:border-green-800">
                          <div className="flex items-center gap-1 mb-1">
                            <CheckCircleIcon className="h-3 w-3" />
                            <strong>Ready for Activation</strong>
                          </div>
                          <div>
                            All deadline categories created. This season can now be activated.
                          </div>
                        </div>
                      )}
                    </div>
                  )}

                  {/* Deadline Status */}
                  {(season.status === 'active' || season.status === 'completed') && (
                    <div className="space-y-2">
                      <div className="flex items-center justify-between">
                        <div className="text-sm font-medium text-foreground">Deadline Status:</div>
                        <div className="text-xs text-muted-foreground">
                          {season.deadlines_count} deadlines
                        </div>
                      </div>
                      
                      <div className="grid grid-cols-1 gap-1 text-xs">
                        {[
                          { key: 'hte_assessment_form', label: 'HTE Assessment Form' },
                          { key: 'student_verification', label: 'Student Verification' },
                          { key: 'student_assessment_form', label: 'Student Assessment Form' },
                          { key: 'internship_placement', label: 'Internship Placement' },
                          { key: 'archive_students', label: 'Archive Students' }
                        ].map((category) => {
                          const status = season.deadline_statuses?.[category.key as keyof typeof season.deadline_statuses] || 'inactive';
                          return (
                            <div key={category.key} className="flex items-center gap-2">
                              <div className={`w-2 h-2 rounded-full ${
                                status === 'active' ? 'bg-green-500' : 
                                status === 'expired' ? 'bg-red-500' :
                                status === 'completed' ? 'bg-blue-500' : 
                                'bg-gray-300'
                              }`} />
                              <span className={`${
                                status === 'active' ? 'text-green-700 dark:text-green-300' : 
                                status === 'expired' ? 'text-red-700 dark:text-red-300' :
                                status === 'completed' ? 'text-blue-700 dark:text-blue-300' : 
                                'text-muted-foreground'
                              }`}>
                                {category.label}
                              </span>
                              <span className={`text-xs ml-auto ${
                                status === 'active' ? 'text-green-600 dark:text-green-400' : 
                                status === 'expired' ? 'text-red-600 dark:text-red-400' :
                                status === 'completed' ? 'text-blue-600 dark:text-blue-400' : 
                                'text-muted-foreground'
                              }`}>
                                {status === 'active' ? 'Active' : 
                                 status === 'expired' ? 'Expired' :
                                 status === 'completed' ? 'Completed' : 
                                 'Inactive'}
                              </span>
                            </div>
                          );
                        })}
                      </div>
                      
                      {season.status === 'active' && (
                        <div className="text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/50 p-2 rounded border border-blue-200 dark:border-blue-800">
                          <div className="flex items-center gap-1 mb-1">
                            <CheckCircleIcon className="h-3 w-3" />
                            <strong>Season Active</strong>
                          </div>
                          <div>
                            Deadlines are currently active and students can submit their requirements.
                          </div>
                        </div>
                      )}
                      
                      {season.status === 'completed' && (
                        <div className="text-xs text-muted-foreground bg-muted/50 p-2 rounded border border-border">
                          <div className="flex items-center gap-1 mb-1">
                            <ClockIcon className="h-3 w-3" />
                            <strong>Season Completed</strong>
                          </div>
                          <div>
                            All deadlines have been processed and the season is now completed.
                          </div>
                        </div>
                      )}
                    </div>
                  )}
                </div>

                {/* Actions - Pushed to bottom */}
                <div className="mt-auto pt-4 space-y-2">
                  {season.status === 'active' ? (
                    <Button
                      size="sm"
                      variant="destructive"
                      onClick={() => handleDeactivateSeason(season)}
                      disabled={isLoading}
                      className="w-full"
                    >
                      Deactivate
                    </Button>
                  ) : season.status === 'inactive' ? (
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => handleActivateSeason(season)}
                      disabled={isLoading || season.deadlines_count < 5}
                      title={season.deadlines_count < 5 ? 'All 5 deadline categories required' : ''}
                      className="w-full"
                    >
                      Activate
                    </Button>
                  ) : null}
                  
                  {season.status === 'completed' && season.active_students_count > 0 && (
                    <Button
                      size="sm"
                      variant="destructive"
                      onClick={() => {
                        if (confirm('Are you sure you want to archive all students in this season? This action cannot be undone.')) {
                          router.post(`/admin/seasons/${season.id}/archive-students`);
                        }
                      }}
                      className="w-full flex items-center gap-2"
                    >
                      <ArchiveIcon className="h-4 w-4" />
                      Archive All Students
                    </Button>
                  )}
                  
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => router.get(`/admin/seasons/${season.id}/stats`)}
                    className="w-full"
                  >
                    View Stats
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Empty State */}
        {seasons.length === 0 && (
          <Card className="text-center py-12">
            <CardContent>
              <CalendarIcon className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
              <h3 className="text-lg font-medium text-foreground mb-2">No Seasons Found</h3>
              <p className="text-muted-foreground mb-4">
                Create your internship season to start managing deadlines and student archiving.
              </p>
            </CardContent>
          </Card>
        )}
      </div>
    </AdminLayout>
  );
}
