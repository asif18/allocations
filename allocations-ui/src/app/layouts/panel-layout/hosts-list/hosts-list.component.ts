/**
 * Copyrights Allocations 2021. All rights reserved
 *
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your
 * business/personal purpose and not to sell or modify
 *
 * Developed by: Mohamed Asif
 * Email: mohamedasif18@gmail.com
 */
import { Component, OnInit, ViewChild, ElementRef, OnDestroy } from '@angular/core';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { Subscription } from 'rxjs';
import { finalize } from 'rxjs/operators';
import { Title } from '@angular/platform-browser';
import { SnackbarService, HostService } from '../../../_services';
import { DefaultApiResponse } from '../../../_models';
import * as _ from 'lodash';

@Component({
  selector: 'app-hosts-list',
  templateUrl: './hosts-list.component.html',
  styleUrls: ['./hosts-list.component.scss']
})
export class HostsListComponent implements OnInit, OnDestroy {

  public displayedColumns: string[] = ['.id', 'comment', 'mac-address', 'to-address', 'idle-time', 'bytes-in',
    'bytes-out'];
  public data: MatTableDataSource<any> = null;
  public isFormLoading: boolean = true;
  public resultsLength = 0;
  private subscriptions: Subscription[] = [];

  @ViewChild(MatPaginator, {static: true}) paginator: MatPaginator;
  @ViewChild(MatSort, {static: true}) sort: MatSort;
  @ViewChild('searchInput', {static: true}) searchInput: ElementRef;

  constructor(
    private titleService: Title,
    private snackBar: SnackbarService,
    private hostService: HostService) {}

  ngOnInit(): void {
    this.titleService.setTitle('Hosts List');
    this.getHosts();
  }

  private getHosts() {
    this.subscriptions.push(this.hostService.getHosts()
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        if (response.status) {
          this.resultsLength = _.size(response.data.items);
          this.data = new MatTableDataSource(response.data.items);
          this.data.paginator = this.paginator;
          this.data.sort = this.sort;
        } else {
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }));
  }

  applyFilter(filterValue: string) {
    this.data.filter = filterValue.trim().toLowerCase();

    if (this.data.paginator) {
      this.data.paginator.firstPage();
    }
  }

  resetSearchValue(): void {
    this.searchInput.nativeElement.value = '';
    this.applyFilter('');
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
