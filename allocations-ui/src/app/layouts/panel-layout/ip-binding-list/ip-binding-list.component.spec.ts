import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { IpBindingListComponent } from './ip-binding-list.component';

describe('IpBindingListComponent', () => {
  let component: IpBindingListComponent;
  let fixture: ComponentFixture<IpBindingListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ IpBindingListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(IpBindingListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
