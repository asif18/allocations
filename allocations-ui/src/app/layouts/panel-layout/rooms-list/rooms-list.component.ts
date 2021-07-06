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
import { Router } from '@angular/router';
import { SnackbarService, UtilityService, AuthenticationService, RoomService } from '../../../_services';
import { DefaultApiResponse, AccessLevels } from '../../../_models';
import * as _ from 'lodash';

@Component({
  selector: 'app-rooms-list',
  templateUrl: './rooms-list.component.html',
  styleUrls: ['./rooms-list.component.scss']
})
export class RoomsListComponent implements OnInit, OnDestroy  {

  public displayedColumns: string[] = ['.id', 'name', 'profile', 'limit-uptime', 'uptime', 'bytes-in',
    'bytes-out', 'limit-bytes-total', 'status', 'action'];
  public data: MatTableDataSource<any> = null;
  public isFormLoading: boolean = true;
  public resultsLength = 0;
  private subscriptions: Subscription[] = [];
  public accessLevels: AccessLevels = {};

  @ViewChild(MatPaginator, {static: true}) paginator: MatPaginator;
  @ViewChild(MatSort, {static: true}) sort: MatSort;
  @ViewChild('searchInput', {static: true}) searchInput: ElementRef;

  constructor(
    private titleService: Title,
    private router: Router,
    private snackBar: SnackbarService,
    private utilityService: UtilityService,
    private roomService: RoomService,
    private authenticationService: AuthenticationService) {}

  ngOnInit(): void {
    this.titleService.setTitle('Rooms List');
    this.getRooms();
    this.loadAccess();
  }

  private loadAccess() {
    this.accessLevels.canRemoveRoom = this.authenticationService.hasAccess('canRemoveRoom');
  }

  private getRooms() {
    this.subscriptions.push(this.roomService.getRooms()
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

  navigate(id: string) {
    this.router.navigate([`panel/send-otp/${btoa(id)}`]);
  }

  changeStatus(room): void {
    if (room.disabled) {
      this.navigate(room['.id']);
    } else {
      this.isFormLoading = true;
      const encryptedId = this.utilityService.base64Encrypt(room['.id']);
      this.subscriptions.push(this.roomService.checkOutRoom(encryptedId)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        this.getRooms();
        this.snackBar.show(response.message, response.status ? 'success' : 'danger');
      }));
    }
  }

  removeRoom(id: string) {
    this.isFormLoading = true;
    const encryptedId = this.utilityService.base64Encrypt(id);
    this.subscriptions.push(this.roomService.removeRoom(encryptedId)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        this.resetSearchValue();
        this.getRooms();
        this.snackBar.show(response.message, response.status ? 'success' : 'danger');
      }));
  }

  encrypt(data: string): string {
    return btoa(data);
  }

  decrypt(data: string): string {
    return atob(data);
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
