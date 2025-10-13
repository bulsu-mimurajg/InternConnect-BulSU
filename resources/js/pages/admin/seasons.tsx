import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { CalendarIcon, PlusIcon, ArchiveIcon, CheckCircleIcon, ClockIcon, UsersIcon, CalendarDaysIcon, ArrowLeftIcon } from 'lucide-react';
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
  status: 'active' | 'completed' | 'archived';
  created_at: string;
  updated_at: string;
  deadlines_count: number;
  students_count: number;
  active_students_count: number;
}

interface Props {
  seasons: InternshipSeason[];
  activeSeason: InternshipSeason | null;
}

export default function SeasonsManagement({ seasons, activeSeason }: Props) {
  const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
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

  const handleActivateSeason = (seasonId: number) => {
    setIsLoading(true);
    router.post(`/admin/seasons/${seasonId}/activate`, {}, {
      onFinish: () => setIsLoading(false),
    });
  };

  const handleArchiveStudents = (seasonId: number) => {
    if (confirm('Are you sure you want to archive all students in this season? This action cannot be undone.')) {
      setIsLoading(true);
      router.post(`/admin/seasons/${seasonId}/archive-students`, {}, {
        onFinish: () => setIsLoading(false),
      });
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'active':
        return <Badge className="bg-green-100 text-green-800">🟢 Active</Badge>;
      case 'completed':
        return <Badge className="bg-blue-100 text-blue-800">✅ Completed</Badge>;
      case 'archived':
        return <Badge className="bg-gray-100 text-gray-800">📁 Archived</Badge>;
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
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div className="flex items-center gap-4">
            <Button
              variant="outline"
              size="sm"
              onClick={() => router.get('/admin/events')}
              className="flex items-center gap-2"
            >
              <ArrowLeftIcon className="h-4 w-4" />
              Back to Events
            </Button>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Internship Seasons</h1>
              <p className="text-gray-600">Manage internship seasons and student archiving</p>
            </div>
          </div>
          
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

        {/* Active Season Info */}
        {activeSeason && (
          <Card className="border-green-200 bg-green-50">
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-green-800">
                <CheckCircleIcon className="h-5 w-5" />
                Current Active Season
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <p className="font-medium text-green-900">{activeSeason.name}</p>
                  <p className="text-sm text-green-700">
                    {formatDate(activeSeason.start_date)} - {formatDate(activeSeason.end_date)}
                  </p>
                </div>
                <div className="flex items-center gap-2">
                  <CalendarDaysIcon className="h-4 w-4 text-green-600" />
                  <span className="text-sm text-green-700">
                    {activeSeason.deadlines_count} deadlines
                  </span>
                </div>
                <div className="flex items-center gap-2">
                  <UsersIcon className="h-4 w-4 text-green-600" />
                  <span className="text-sm text-green-700">
                    {activeSeason.active_students_count} active students
                  </span>
                </div>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Seasons List */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {seasons.map((season) => (
            <Card key={season.id} className="relative">
              <CardHeader>
                <div className="flex items-start justify-between">
                  <div className="space-y-1">
                    <CardTitle className="text-lg">{season.name}</CardTitle>
                    <CardDescription>
                      {formatDate(season.start_date)} - {formatDate(season.end_date)}
                    </CardDescription>
                  </div>
                  {getStatusBadge(season.status)}
                </div>
              </CardHeader>
              
              <CardContent className="space-y-4">
                {/* Statistics */}
                <div className="grid grid-cols-2 gap-4 text-sm">
                  <div className="flex items-center gap-2">
                    <CalendarDaysIcon className="h-4 w-4 text-gray-500" />
                    <span>{season.deadlines_count} deadlines</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <UsersIcon className="h-4 w-4 text-gray-500" />
                    <span>{season.students_count} students</span>
                  </div>
                </div>

                {/* Actions */}
                <div className="flex flex-wrap gap-2">
                  {season.status !== 'active' && (
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => handleActivateSeason(season.id)}
                      disabled={isLoading}
                    >
                      <CheckCircleIcon className="h-4 w-4 mr-1" />
                      Activate
                    </Button>
                  )}
                  
                  {season.status === 'completed' && (
                    <Button
                      size="sm"
                      variant="destructive"
                      onClick={() => handleArchiveStudents(season.id)}
                      disabled={isLoading}
                    >
                      <ArchiveIcon className="h-4 w-4 mr-1" />
                      Archive Students
                    </Button>
                  )}
                  
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => router.get(`/admin/seasons/${season.id}/stats`)}
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
              <CalendarIcon className="h-12 w-12 text-gray-400 mx-auto mb-4" />
              <h3 className="text-lg font-medium text-gray-900 mb-2">No Seasons Found</h3>
              <p className="text-gray-600 mb-4">
                Create your first internship season to start managing deadlines and student archiving.
              </p>
              <Button onClick={() => setIsCreateDialogOpen(true)}>
                <PlusIcon className="h-4 w-4 mr-2" />
                Create First Season
              </Button>
            </CardContent>
          </Card>
        )}
      </div>
    </AdminLayout>
  );
}
