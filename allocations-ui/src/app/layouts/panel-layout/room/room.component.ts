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
import { Component, OnInit, OnDestroy } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
import { noop, Subscription } from 'rxjs';
import { finalize } from 'rxjs/operators';
import { SnackbarService, RoomService, ProfileService } from '../../../_services';
import { Title } from '@angular/platform-browser';
import { DefaultApiResponse } from '../../../_models';
import * as _ from 'lodash';

@Component({
  selector: 'app-add-room',
  templateUrl: './room.component.html',
  styleUrls: ['./room.component.scss']
})
export class RoomComponent implements OnInit, OnDestroy {

  public isFormLoading: boolean = true;
  public form: FormGroup;
  private submitted = false;
  private subscriptions: Subscription[] = [];
  public profiles: object[] = [];

  constructor(
    private formBuilder: FormBuilder,
    private roomService: RoomService,
    private profileService: ProfileService,
    private snackBar: SnackbarService,
    private titleService: Title
  ) { }

  ngOnInit(): void {
    this.titleService.setTitle('Add Room');
    this.initForm();
    this.getProfiles();
  }

  private getProfiles() {
    this.subscriptions.push(this.profileService.getProfiles()
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        if (response.status) {
          this.profiles = response.data.items;
        } else {
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }));
  }

  private initForm() {
    this.form = this.formBuilder.group({
      profile: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      username: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(35),
          Validators.pattern(/^\S*$/)
        ]
      }),
      password: new FormControl(null, {
        validators: [
          Validators.required, 
          Validators.maxLength(15),
          Validators.minLength(5),
          Validators.pattern(/^\S*$/)
        ]
      })
    });
  }

  get f() {
    return this.form.controls;
  }

  onSubmit() {
    this.submitted = true;

    if (this.form.invalid) {
      return;
    }

    this.isFormLoading = true;

    this.form.value.password = btoa(this.form.value.password);

    this.subscriptions.push(this.roomService.addRoom(this.form.value)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: any) => {
        if (response) {
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }, () => noop()));
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
